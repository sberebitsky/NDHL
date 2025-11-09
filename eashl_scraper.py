import requests
import time
import mysql.connector
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry

# === CONFIG ===
CLUB_IDS = ["54731"]
PLATFORM = "common-gen5"
MATCH_TYPE = "club_private"

HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
    "Accept": "application/json, text/javascript, */*; q=0.01",
    "Referer": "https://www.ea.com/",
    "Origin": "https://www.ea.com"
}

DB_CONFIG = {
    "host": "db.luddy.indiana.edu",
    "user": "i308f25_sambereb",
    "password": "nutty0606spuds",
    "database": "i308f25_sambereb"
}

# === HELPERS ===
def make_session():
    session = requests.Session()
    retries = Retry(total=5, backoff_factor=1, status_forcelist=[429, 500, 502, 503, 504], allowed_methods=["GET"])
    session.mount("https://", HTTPAdapter(max_retries=retries))
    return session

def connect_db():
    try:
        return mysql.connector.connect(**DB_CONFIG)
    except mysql.connector.Error as err:
        print(f"❌ Database connection error: {err}")
        exit(1)

# === DB INSERT FUNCTIONS ===

def save_match(cursor, match_id, timestamp, home_id, away_id, home_score, away_score):
    cursor.execute("""
        INSERT INTO eashl_matches (match_id, timestamp, home_club_id, away_club_id, home_score, away_score)
        VALUES (%s, FROM_UNIXTIME(%s), %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE
            home_score = VALUES(home_score),
            away_score = VALUES(away_score)
    """, (match_id, timestamp, home_id, away_id, home_score, away_score))

def save_team_stats(cursor, match_id, club_id, team_data):
    cursor.execute("""
        INSERT INTO eashl_team_stats (
            match_id, club_id, goals, shots, passes_completed, passes_attempted, pp_goals, pp_opportunities,
            time_on_attack, result
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE
            goals = VALUES(goals),
            shots = VALUES(shots),
            passes_completed = VALUES(passes_completed),
            passes_attempted = VALUES(passes_attempted),
            result = VALUES(result)
    """, (
        match_id, club_id,
        int(team_data.get("goals", 0)),
        int(team_data.get("shots", 0)),
        int(team_data.get("passc", 0)),
        int(team_data.get("passa", 0)),
        int(team_data.get("ppg", 0)),
        int(team_data.get("ppo", 0)),
        int(team_data.get("toa", 0)),
        team_data.get("result", "N/A")
    ))

def save_player_stats(cursor, match_id, club_id, player_id, p):
    cursor.execute("""
        INSERT INTO eashl_player_stats (
            match_id, club_id, player_id, player_name, position, goals, assists, shots,
            hits, giveaways, takeaways, plus_minus, faceoffs_won, faceoffs_lost,
            save_pct, goals_against, time_on_ice
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE
            goals = VALUES(goals),
            assists = VALUES(assists),
            shots = VALUES(shots),
            hits = VALUES(hits),
            plus_minus = VALUES(plus_minus)
    """, (
        match_id, club_id, player_id, p.get("playername", "Unknown"),
        p.get("position", "Unknown"),
        int(p.get("skgoals", 0)),
        int(p.get("skassists", 0)),
        int(p.get("skshots", 0)),
        int(p.get("skhits", 0)),
        int(p.get("skgiveaways", 0)),
        int(p.get("sktakeaways", 0)),
        int(p.get("skplusmin", 0)),
        int(p.get("skfow", 0)),
        int(p.get("skfol", 0)),
        float(p.get("glsavepct", 0.0)),
        int(p.get("glga", 0)),
        int(p.get("toiseconds", 0))
    ))

# === FETCH + PROCESS ===

def fetch_matches(session, club_id, cursor):
    url = f"https://proclubs.ea.com/api/nhl/clubs/matches?matchType={MATCH_TYPE}&platform={PLATFORM}&clubIds={club_id}"
    print(f"\n📡 Fetching matches for club {club_id} ...")
    try:
        resp = session.get(url, headers=HEADERS, timeout=20)
        resp.raise_for_status()
        matches = resp.json()
    except Exception as e:
        print(f"❌ Error fetching matches: {e}")
        return

    for match in matches:
        match_id = match.get("matchId")
        timestamp = match.get("timestamp")

        # Clubs section
        clubs = match.get("clubs", {})
        club_ids = list(clubs.keys())
        if len(club_ids) != 2:
            continue

        home_id, away_id = club_ids
        home_data = clubs[home_id]
        away_data = clubs[away_id]

        # Insert match summary
        save_match(cursor, match_id, timestamp, home_id, away_id, home_data.get("score"), away_data.get("score"))

        # Insert team stats
        save_team_stats(cursor, match_id, home_id, home_data)
        save_team_stats(cursor, match_id, away_id, away_data)

        # Player stats
        players = match.get("players", {})
        for c_id, roster in players.items():
            for pid, pdata in roster.items():
                save_player_stats(cursor, match_id, c_id, pid, pdata)

        print(f"✅ Match {match_id} processed.")

# === MAIN ===

def main():
    session = make_session()
    db = connect_db()
    cursor = db.cursor()

    for club_id in CLUB_IDS:
        fetch_matches(session, club_id, cursor)
        db.commit()
        time.sleep(2)

    cursor.close()
    db.close()
    print("\n✅ All clubs processed!")

if __name__ == "__main__":
    main()
