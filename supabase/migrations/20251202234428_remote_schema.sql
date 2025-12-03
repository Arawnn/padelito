


SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;


COMMENT ON SCHEMA "public" IS 'standard public schema';



CREATE EXTENSION IF NOT EXISTS "pg_graphql" WITH SCHEMA "graphql";






CREATE EXTENSION IF NOT EXISTS "pg_stat_statements" WITH SCHEMA "extensions";






CREATE EXTENSION IF NOT EXISTS "pgcrypto" WITH SCHEMA "extensions";






CREATE EXTENSION IF NOT EXISTS "supabase_vault" WITH SCHEMA "vault";






CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA "extensions";






CREATE OR REPLACE FUNCTION "public"."are_friends"("p_user1_id" "uuid", "p_user2_id" "uuid") RETURNS boolean
    LANGUAGE "plpgsql" SECURITY DEFINER
    SET "search_path" TO 'public', 'pg_temp'
    AS $$
BEGIN
  RETURN EXISTS (
    SELECT 1
    FROM public.friendships f
    WHERE (
      (f.user_id = p_user1_id AND f.friend_id = p_user2_id)
      OR (f.user_id = p_user2_id AND f.friend_id = p_user1_id)
    )
    AND f.status = 'accepted'
  );
END;
$$;


ALTER FUNCTION "public"."are_friends"("p_user1_id" "uuid", "p_user2_id" "uuid") OWNER TO "postgres";


CREATE OR REPLACE FUNCTION "public"."can_bet_on_match"("p_user_id" "uuid", "p_match_id" "uuid") RETURNS boolean
    LANGUAGE "plpgsql" SECURITY DEFINER
    AS $$
BEGIN
  -- Ne peut pas parier sur ses propres matchs
  IF is_match_participant(p_user_id, p_match_id) THEN
    RETURN FALSE;
  END IF;
  
  -- Ne peut pas parier après le début du match
  IF EXISTS (
    SELECT 1 FROM matches 
    WHERE id = p_match_id AND match_date <= NOW()
  ) THEN
    RETURN FALSE;
  END IF;
  
  RETURN TRUE;
END;
$$;


ALTER FUNCTION "public"."can_bet_on_match"("p_user_id" "uuid", "p_match_id" "uuid") OWNER TO "postgres";


CREATE OR REPLACE FUNCTION "public"."has_sufficient_coins"("p_user_id" "uuid", "p_amount" integer) RETURNS boolean
    LANGUAGE "plpgsql" SECURITY DEFINER
    AS $$
BEGIN
  RETURN EXISTS (
    SELECT 1 FROM profiles 
    WHERE id = p_user_id AND padel_coins >= p_amount
  );
END;
$$;


ALTER FUNCTION "public"."has_sufficient_coins"("p_user_id" "uuid", "p_amount" integer) OWNER TO "postgres";


CREATE OR REPLACE FUNCTION "public"."is_fantasy_league_admin"("p_user_id" "uuid", "p_league_id" "uuid") RETURNS boolean
    LANGUAGE "plpgsql" SECURITY DEFINER
    AS $$
BEGIN
  RETURN EXISTS (
    SELECT 1 FROM fantasy_league_members flm
    JOIN fantasy_teams ft ON flm.team_id = ft.id
    WHERE flm.league_id = p_league_id 
      AND ft.user_id = p_user_id 
      AND flm.is_admin = true
  );
END;
$$;


ALTER FUNCTION "public"."is_fantasy_league_admin"("p_user_id" "uuid", "p_league_id" "uuid") OWNER TO "postgres";


CREATE OR REPLACE FUNCTION "public"."is_fantasy_league_member"("p_user_id" "uuid", "p_league_id" "uuid") RETURNS boolean
    LANGUAGE "plpgsql" SECURITY DEFINER
    AS $$
BEGIN
  RETURN EXISTS (
    SELECT 1 FROM fantasy_league_members flm
    JOIN fantasy_teams ft ON flm.team_id = ft.id
    WHERE flm.league_id = p_league_id AND ft.user_id = p_user_id
  );
END;
$$;


ALTER FUNCTION "public"."is_fantasy_league_member"("p_user_id" "uuid", "p_league_id" "uuid") OWNER TO "postgres";


CREATE OR REPLACE FUNCTION "public"."is_group_admin"("p_user_id" "uuid", "p_group_id" "uuid") RETURNS boolean
    LANGUAGE "plpgsql" SECURITY DEFINER
    AS $$
BEGIN
  RETURN EXISTS (
    SELECT 1 FROM group_members 
    WHERE group_id = p_group_id 
      AND user_id = p_user_id 
      AND role = 'admin'
  );
END;
$$;


ALTER FUNCTION "public"."is_group_admin"("p_user_id" "uuid", "p_group_id" "uuid") OWNER TO "postgres";


CREATE OR REPLACE FUNCTION "public"."is_match_participant"("p_user_id" "uuid", "p_match_id" "uuid") RETURNS boolean
    LANGUAGE "plpgsql" SECURITY DEFINER
    AS $$
BEGIN
  RETURN EXISTS (
    SELECT 1 FROM matches
    WHERE id = p_match_id AND (
      team_a_player1_id = p_user_id OR
      team_a_player2_id = p_user_id OR
      team_b_player1_id = p_user_id OR
      team_b_player2_id = p_user_id
    )
  );
END;
$$;


ALTER FUNCTION "public"."is_match_participant"("p_user_id" "uuid", "p_match_id" "uuid") OWNER TO "postgres";


CREATE OR REPLACE FUNCTION "public"."is_tournament_participant"("p_user_id" "uuid", "p_tournament_id" "uuid") RETURNS boolean
    LANGUAGE "plpgsql" SECURITY DEFINER
    AS $$
BEGIN
  RETURN EXISTS (
    SELECT 1 FROM tournament_registrations
    WHERE tournament_id = p_tournament_id
      AND (player1_id = p_user_id OR player2_id = p_user_id)
  );
END;
$$;


ALTER FUNCTION "public"."is_tournament_participant"("p_user_id" "uuid", "p_tournament_id" "uuid") OWNER TO "postgres";


CREATE OR REPLACE FUNCTION "public"."owns_fantasy_team"("p_user_id" "uuid", "p_team_id" "uuid") RETURNS boolean
    LANGUAGE "plpgsql" SECURITY DEFINER
    AS $$
BEGIN
  RETURN EXISTS (
    SELECT 1 FROM fantasy_teams 
    WHERE id = p_team_id AND user_id = p_user_id
  );
END;
$$;


ALTER FUNCTION "public"."owns_fantasy_team"("p_user_id" "uuid", "p_team_id" "uuid") OWNER TO "postgres";


CREATE OR REPLACE FUNCTION "public"."prevent_sensitive_data_modification"() RETURNS "trigger"
    LANGUAGE "plpgsql"
    AS $$
BEGIN
  -- Empêcher modification directe des données sensibles
  IF OLD.elo_rating IS DISTINCT FROM NEW.elo_rating THEN
    RAISE EXCEPTION 'Modification directe de elo_rating interdite. Utilisez le système ELO.';
  END IF;
  
  IF OLD.padel_coins IS DISTINCT FROM NEW.padel_coins THEN
    RAISE EXCEPTION 'Modification directe de padel_coins interdite. Utilisez le système de transactions.';
  END IF;
  
  IF OLD.total_matches IS DISTINCT FROM NEW.total_matches THEN
    RAISE EXCEPTION 'Modification directe de total_matches interdite.';
  END IF;
  
  IF OLD.total_wins IS DISTINCT FROM NEW.total_wins THEN
    RAISE EXCEPTION 'Modification directe de total_wins interdite.';
  END IF;
  
  IF OLD.total_losses IS DISTINCT FROM NEW.total_losses THEN
    RAISE EXCEPTION 'Modification directe de total_losses interdite.';
  END IF;
  
  IF OLD.current_streak IS DISTINCT FROM NEW.current_streak THEN
    RAISE EXCEPTION 'Modification directe de current_streak interdite.';
  END IF;
  
  IF OLD.best_streak IS DISTINCT FROM NEW.best_streak THEN
    RAISE EXCEPTION 'Modification directe de best_streak interdite.';
  END IF;
  
  RETURN NEW;
END;
$$;


ALTER FUNCTION "public"."prevent_sensitive_data_modification"() OWNER TO "postgres";


CREATE OR REPLACE FUNCTION "public"."update_fantasy_rankings"() RETURNS "void"
    LANGUAGE "plpgsql"
    AS $$BEGIN
  -- Ensure a stable search_path during execution to avoid role-mutable search_path warnings
  PERFORM set_config('search_path', 'pg_catalog, public', true);

  WITH ranked_teams AS (
    SELECT 
      id,
      ROW_NUMBER() OVER (PARTITION BY season_id ORDER BY total_points DESC) AS new_rank
    FROM public.fantasy_teams
  )
  UPDATE public.fantasy_teams ft
  SET global_rank = rt.new_rank
  FROM ranked_teams rt
  WHERE ft.id = rt.id;
END;$$;


ALTER FUNCTION "public"."update_fantasy_rankings"() OWNER TO "postgres";


CREATE OR REPLACE FUNCTION "public"."update_updated_at_column"() RETURNS "trigger"
    LANGUAGE "plpgsql"
    AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$;


ALTER FUNCTION "public"."update_updated_at_column"() OWNER TO "postgres";

SET default_tablespace = '';

SET default_table_access_method = "heap";


CREATE TABLE IF NOT EXISTS "public"."achievements" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "code" character varying(50) NOT NULL,
    "name" character varying(100) NOT NULL,
    "description" "text",
    "icon_url" "text",
    "rarity" character varying(20) DEFAULT 'common'::character varying,
    "criteria" "jsonb",
    "reward_coins" integer DEFAULT 0
);


ALTER TABLE "public"."achievements" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."activity_feed" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "user_id" "uuid",
    "activity_type" character varying(50) NOT NULL,
    "content" "jsonb" NOT NULL,
    "visibility" character varying(20) DEFAULT 'friends'::character varying,
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."activity_feed" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."activity_reactions" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "activity_id" "uuid",
    "user_id" "uuid",
    "reaction_type" character varying(20) NOT NULL,
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."activity_reactions" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."bets" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "match_id" "uuid",
    "bettor_id" "uuid",
    "predicted_winner" character varying(10) NOT NULL,
    "bet_amount" integer NOT NULL,
    "odds" numeric(5,2) NOT NULL,
    "status" character varying(20) DEFAULT 'pending'::character varying,
    "potential_win" integer,
    "actual_win" integer,
    "placed_at" timestamp with time zone DEFAULT "now"(),
    "resolved_at" timestamp with time zone,
    CONSTRAINT "bets_bet_amount_check" CHECK (("bet_amount" > 0))
);


ALTER TABLE "public"."bets" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."championship_rounds" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "championship_id" "uuid",
    "round_number" integer NOT NULL,
    "round_date" "date",
    "status" character varying(20) DEFAULT 'upcoming'::character varying
);


ALTER TABLE "public"."championship_rounds" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."championship_standings" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "championship_id" "uuid",
    "registration_id" "uuid",
    "matches_played" integer DEFAULT 0,
    "matches_won" integer DEFAULT 0,
    "matches_lost" integer DEFAULT 0,
    "sets_won" integer DEFAULT 0,
    "sets_lost" integer DEFAULT 0,
    "games_won" integer DEFAULT 0,
    "games_lost" integer DEFAULT 0,
    "points" integer DEFAULT 0
);


ALTER TABLE "public"."championship_standings" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."championships" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "name" character varying(200) NOT NULL,
    "description" "text",
    "status" character varying(20) DEFAULT 'active'::character varying,
    "group_id" "uuid",
    "start_date" "date",
    "end_date" "date",
    "created_by" "uuid",
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."championships" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."coin_transactions" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "user_id" "uuid",
    "amount" integer NOT NULL,
    "transaction_type" character varying(50) NOT NULL,
    "reference_id" "uuid",
    "description" "text",
    "balance_after" integer NOT NULL,
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."coin_transactions" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."comments" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "activity_id" "uuid",
    "user_id" "uuid",
    "content" "text" NOT NULL,
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."comments" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."elo_history" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "user_id" "uuid",
    "match_id" "uuid",
    "elo_before" integer NOT NULL,
    "elo_after" integer NOT NULL,
    "elo_change" integer NOT NULL,
    "recorded_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."elo_history" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_achievements" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "code" character varying(50) NOT NULL,
    "name" character varying(100) NOT NULL,
    "description" "text",
    "icon_url" "text",
    "rarity" character varying(20) DEFAULT 'common'::character varying,
    "criteria" "jsonb"
);


ALTER TABLE "public"."fantasy_achievements" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_chips" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "code" character varying(50) NOT NULL,
    "name" character varying(100) NOT NULL,
    "description" "text",
    "effect_type" character varying(50) NOT NULL,
    "can_use_multiple_times" boolean DEFAULT false,
    "max_uses_per_season" integer DEFAULT 1
);


ALTER TABLE "public"."fantasy_chips" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_gameweek_rankings" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "gameweek_id" "uuid",
    "team_id" "uuid",
    "points" integer DEFAULT 0,
    "rank" integer,
    "rank_change" integer DEFAULT 0,
    "points_change" integer DEFAULT 0,
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."fantasy_gameweek_rankings" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_gameweeks" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "season_id" "uuid",
    "gameweek_number" integer NOT NULL,
    "name" character varying(100),
    "start_date" timestamp with time zone NOT NULL,
    "end_date" timestamp with time zone NOT NULL,
    "deadline" timestamp with time zone NOT NULL,
    "is_active" boolean DEFAULT false,
    "is_completed" boolean DEFAULT false,
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."fantasy_gameweeks" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_league_members" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "league_id" "uuid",
    "team_id" "uuid",
    "joined_at" timestamp with time zone DEFAULT "now"(),
    "is_admin" boolean DEFAULT false
);


ALTER TABLE "public"."fantasy_league_members" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_leagues" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "season_id" "uuid",
    "name" character varying(200) NOT NULL,
    "description" "text",
    "league_code" character varying(20) NOT NULL,
    "type" character varying(20) DEFAULT 'private'::character varying,
    "max_teams" integer DEFAULT 20,
    "prize_pool" integer DEFAULT 0,
    "prizes" "jsonb",
    "created_by" "uuid",
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."fantasy_leagues" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_news" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "season_id" "uuid",
    "title" character varying(200) NOT NULL,
    "content" "text" NOT NULL,
    "news_type" character varying(50),
    "related_players" "uuid"[],
    "is_featured" boolean DEFAULT false,
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."fantasy_news" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_player_performances" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "player_id" "uuid",
    "gameweek_id" "uuid",
    "match_id" "uuid",
    "played" boolean DEFAULT true,
    "victory" boolean DEFAULT false,
    "games_won" integer DEFAULT 0,
    "games_lost" integer DEFAULT 0,
    "clean_sheet" boolean DEFAULT false,
    "is_mvp" boolean DEFAULT false,
    "is_in_hat_trick" boolean DEFAULT false,
    "base_points" integer DEFAULT 0,
    "bonus_points" integer DEFAULT 0,
    "total_points" integer DEFAULT 0,
    "notes" "text",
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."fantasy_player_performances" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_player_price_history" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "player_id" "uuid",
    "gameweek_id" "uuid",
    "old_price" integer NOT NULL,
    "new_price" integer NOT NULL,
    "reason" "text",
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."fantasy_player_price_history" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_players" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "season_id" "uuid",
    "user_id" "uuid",
    "player_name" character varying(100) NOT NULL,
    "position" character varying(20) DEFAULT 'player'::character varying,
    "club_name" character varying(100),
    "initial_price" integer NOT NULL,
    "current_price" integer NOT NULL,
    "elo_rating" integer,
    "total_points" integer DEFAULT 0,
    "matches_played" integer DEFAULT 0,
    "wins" integer DEFAULT 0,
    "losses" integer DEFAULT 0,
    "recent_form" "jsonb",
    "is_available" boolean DEFAULT true,
    "is_injured" boolean DEFAULT false,
    "injury_return_date" "date",
    "created_at" timestamp with time zone DEFAULT "now"(),
    "updated_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."fantasy_players" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_seasons" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "name" character varying(100) NOT NULL,
    "description" "text",
    "status" character varying(20) DEFAULT 'upcoming'::character varying,
    "start_date" "date" NOT NULL,
    "end_date" "date" NOT NULL,
    "registration_deadline" "date",
    "budget" integer DEFAULT 500,
    "team_size" integer DEFAULT 8,
    "max_same_club" integer DEFAULT 3,
    "points_victory" integer DEFAULT 3,
    "points_defeat" integer DEFAULT 0,
    "points_goals_for" integer DEFAULT 1,
    "points_goals_against" integer DEFAULT '-1'::integer,
    "points_clean_sheet" integer DEFAULT 5,
    "points_hat_trick" integer DEFAULT 10,
    "points_mvp" integer DEFAULT 15,
    "created_at" timestamp with time zone DEFAULT "now"(),
    "updated_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."fantasy_seasons" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_team_achievements" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "team_id" "uuid",
    "achievement_id" "uuid",
    "unlocked_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."fantasy_team_achievements" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_team_chips" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "team_id" "uuid",
    "chip_id" "uuid",
    "gameweek_id" "uuid",
    "used_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."fantasy_team_chips" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_team_rosters" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "team_id" "uuid",
    "player_id" "uuid",
    "is_captain" boolean DEFAULT false,
    "is_vice_captain" boolean DEFAULT false,
    "is_benched" boolean DEFAULT false,
    "position_order" integer,
    "acquired_at" timestamp with time zone DEFAULT "now"(),
    "acquisition_price" integer NOT NULL
);


ALTER TABLE "public"."fantasy_team_rosters" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_teams" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "season_id" "uuid",
    "user_id" "uuid",
    "team_name" character varying(100) NOT NULL,
    "team_logo" "text",
    "initial_budget" integer DEFAULT 500,
    "remaining_budget" integer DEFAULT 500,
    "team_value" integer DEFAULT 0,
    "total_points" integer DEFAULT 0,
    "global_rank" integer,
    "formation" character varying(20) DEFAULT 'balanced'::character varying,
    "created_at" timestamp with time zone DEFAULT "now"(),
    "updated_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."fantasy_teams" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."fantasy_transfers" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "team_id" "uuid",
    "gameweek_id" "uuid",
    "player_out_id" "uuid",
    "player_in_id" "uuid",
    "transfer_cost" integer DEFAULT 0,
    "transfer_type" character varying(20) DEFAULT 'standard'::character varying,
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."fantasy_transfers" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."friendships" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "user_id" "uuid",
    "friend_id" "uuid",
    "status" character varying(20) DEFAULT 'pending'::character varying,
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."friendships" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."group_members" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "group_id" "uuid",
    "user_id" "uuid",
    "role" character varying(20) DEFAULT 'member'::character varying,
    "joined_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."group_members" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."groups" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "name" character varying(100) NOT NULL,
    "description" "text",
    "avatar_url" "text",
    "created_by" "uuid",
    "is_private" boolean DEFAULT false,
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."groups" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."matches" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "match_date" timestamp with time zone DEFAULT "now"(),
    "court_name" character varying(100),
    "match_type" character varying(20) DEFAULT 'friendly'::character varying,
    "tournament_id" "uuid",
    "team_a_player1_id" "uuid",
    "team_a_player2_id" "uuid",
    "team_a_score" integer NOT NULL,
    "team_b_player1_id" "uuid",
    "team_b_player2_id" "uuid",
    "team_b_score" integer NOT NULL,
    "sets_detail" "jsonb",
    "team_a_elo_before" integer,
    "team_b_elo_before" integer,
    "elo_change" integer,
    "notes" "text",
    "photos" "text"[],
    "is_validated" boolean DEFAULT true,
    "created_by" "uuid",
    "created_at" timestamp with time zone DEFAULT "now"(),
    CONSTRAINT "matches_check" CHECK ((("team_a_score" >= 0) AND ("team_b_score" >= 0)))
);


ALTER TABLE "public"."matches" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."notifications" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "user_id" "uuid",
    "notification_type" character varying(50) NOT NULL,
    "title" character varying(200) NOT NULL,
    "body" "text",
    "data" "jsonb",
    "is_read" boolean DEFAULT false,
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."notifications" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."profiles" (
    "id" "uuid" NOT NULL,
    "username" character varying(50) NOT NULL,
    "display_name" character varying(100),
    "avatar_url" "text",
    "bio" "text",
    "elo_rating" integer DEFAULT 1500,
    "padel_coins" integer DEFAULT 1000,
    "total_matches" integer DEFAULT 0,
    "total_wins" integer DEFAULT 0,
    "total_losses" integer DEFAULT 0,
    "current_streak" integer DEFAULT 0,
    "best_streak" integer DEFAULT 0,
    "created_at" timestamp with time zone DEFAULT "now"(),
    "updated_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."profiles" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."shop_items" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "item_type" character varying(50) NOT NULL,
    "name" character varying(100) NOT NULL,
    "description" "text",
    "image_url" "text",
    "price" integer NOT NULL,
    "rarity" character varying(20) DEFAULT 'common'::character varying,
    "is_available" boolean DEFAULT true,
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."shop_items" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."tournament_brackets" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "tournament_id" "uuid",
    "round" character varying(50),
    "match_number" integer,
    "team_a_registration_id" "uuid",
    "team_b_registration_id" "uuid",
    "match_id" "uuid",
    "winner_registration_id" "uuid",
    "next_bracket_id" "uuid"
);


ALTER TABLE "public"."tournament_brackets" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."tournament_registrations" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "tournament_id" "uuid",
    "player1_id" "uuid",
    "player2_id" "uuid",
    "team_name" character varying(100),
    "registration_date" timestamp with time zone DEFAULT "now"(),
    "seed" integer
);


ALTER TABLE "public"."tournament_registrations" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."tournaments" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "name" character varying(200) NOT NULL,
    "description" "text",
    "tournament_type" character varying(20) DEFAULT 'knockout'::character varying,
    "status" character varying(20) DEFAULT 'upcoming'::character varying,
    "max_teams" integer DEFAULT 8,
    "start_date" timestamp with time zone,
    "end_date" timestamp with time zone,
    "group_id" "uuid",
    "created_by" "uuid",
    "settings" "jsonb",
    "created_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."tournaments" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."user_achievements" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "user_id" "uuid",
    "achievement_id" "uuid",
    "unlocked_at" timestamp with time zone DEFAULT "now"()
);


ALTER TABLE "public"."user_achievements" OWNER TO "postgres";


CREATE TABLE IF NOT EXISTS "public"."user_inventory" (
    "id" "uuid" DEFAULT "gen_random_uuid"() NOT NULL,
    "user_id" "uuid",
    "item_id" "uuid",
    "purchased_at" timestamp with time zone DEFAULT "now"(),
    "is_equipped" boolean DEFAULT false
);


ALTER TABLE "public"."user_inventory" OWNER TO "postgres";


ALTER TABLE ONLY "public"."achievements"
    ADD CONSTRAINT "achievements_code_key" UNIQUE ("code");



ALTER TABLE ONLY "public"."achievements"
    ADD CONSTRAINT "achievements_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."activity_feed"
    ADD CONSTRAINT "activity_feed_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."activity_reactions"
    ADD CONSTRAINT "activity_reactions_activity_id_user_id_key" UNIQUE ("activity_id", "user_id");



ALTER TABLE ONLY "public"."activity_reactions"
    ADD CONSTRAINT "activity_reactions_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."bets"
    ADD CONSTRAINT "bets_match_id_bettor_id_key" UNIQUE ("match_id", "bettor_id");



ALTER TABLE ONLY "public"."bets"
    ADD CONSTRAINT "bets_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."championship_rounds"
    ADD CONSTRAINT "championship_rounds_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."championship_standings"
    ADD CONSTRAINT "championship_standings_championship_id_registration_id_key" UNIQUE ("championship_id", "registration_id");



ALTER TABLE ONLY "public"."championship_standings"
    ADD CONSTRAINT "championship_standings_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."championships"
    ADD CONSTRAINT "championships_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."coin_transactions"
    ADD CONSTRAINT "coin_transactions_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."comments"
    ADD CONSTRAINT "comments_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."elo_history"
    ADD CONSTRAINT "elo_history_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_achievements"
    ADD CONSTRAINT "fantasy_achievements_code_key" UNIQUE ("code");



ALTER TABLE ONLY "public"."fantasy_achievements"
    ADD CONSTRAINT "fantasy_achievements_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_chips"
    ADD CONSTRAINT "fantasy_chips_code_key" UNIQUE ("code");



ALTER TABLE ONLY "public"."fantasy_chips"
    ADD CONSTRAINT "fantasy_chips_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_gameweek_rankings"
    ADD CONSTRAINT "fantasy_gameweek_rankings_gameweek_id_team_id_key" UNIQUE ("gameweek_id", "team_id");



ALTER TABLE ONLY "public"."fantasy_gameweek_rankings"
    ADD CONSTRAINT "fantasy_gameweek_rankings_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_gameweeks"
    ADD CONSTRAINT "fantasy_gameweeks_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_gameweeks"
    ADD CONSTRAINT "fantasy_gameweeks_season_id_gameweek_number_key" UNIQUE ("season_id", "gameweek_number");



ALTER TABLE ONLY "public"."fantasy_league_members"
    ADD CONSTRAINT "fantasy_league_members_league_id_team_id_key" UNIQUE ("league_id", "team_id");



ALTER TABLE ONLY "public"."fantasy_league_members"
    ADD CONSTRAINT "fantasy_league_members_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_leagues"
    ADD CONSTRAINT "fantasy_leagues_league_code_key" UNIQUE ("league_code");



ALTER TABLE ONLY "public"."fantasy_leagues"
    ADD CONSTRAINT "fantasy_leagues_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_news"
    ADD CONSTRAINT "fantasy_news_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_player_performances"
    ADD CONSTRAINT "fantasy_player_performances_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_player_performances"
    ADD CONSTRAINT "fantasy_player_performances_player_id_gameweek_id_match_id_key" UNIQUE ("player_id", "gameweek_id", "match_id");



ALTER TABLE ONLY "public"."fantasy_player_price_history"
    ADD CONSTRAINT "fantasy_player_price_history_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_players"
    ADD CONSTRAINT "fantasy_players_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_players"
    ADD CONSTRAINT "fantasy_players_season_id_user_id_key" UNIQUE ("season_id", "user_id");



ALTER TABLE ONLY "public"."fantasy_seasons"
    ADD CONSTRAINT "fantasy_seasons_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_team_achievements"
    ADD CONSTRAINT "fantasy_team_achievements_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_team_achievements"
    ADD CONSTRAINT "fantasy_team_achievements_team_id_achievement_id_key" UNIQUE ("team_id", "achievement_id");



ALTER TABLE ONLY "public"."fantasy_team_chips"
    ADD CONSTRAINT "fantasy_team_chips_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_team_chips"
    ADD CONSTRAINT "fantasy_team_chips_team_id_chip_id_gameweek_id_key" UNIQUE ("team_id", "chip_id", "gameweek_id");



ALTER TABLE ONLY "public"."fantasy_team_rosters"
    ADD CONSTRAINT "fantasy_team_rosters_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_team_rosters"
    ADD CONSTRAINT "fantasy_team_rosters_team_id_player_id_key" UNIQUE ("team_id", "player_id");



ALTER TABLE ONLY "public"."fantasy_teams"
    ADD CONSTRAINT "fantasy_teams_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."fantasy_teams"
    ADD CONSTRAINT "fantasy_teams_season_id_user_id_key" UNIQUE ("season_id", "user_id");



ALTER TABLE ONLY "public"."fantasy_transfers"
    ADD CONSTRAINT "fantasy_transfers_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."friendships"
    ADD CONSTRAINT "friendships_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."friendships"
    ADD CONSTRAINT "friendships_user_id_friend_id_key" UNIQUE ("user_id", "friend_id");



ALTER TABLE ONLY "public"."group_members"
    ADD CONSTRAINT "group_members_group_id_user_id_key" UNIQUE ("group_id", "user_id");



ALTER TABLE ONLY "public"."group_members"
    ADD CONSTRAINT "group_members_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."groups"
    ADD CONSTRAINT "groups_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."matches"
    ADD CONSTRAINT "matches_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."notifications"
    ADD CONSTRAINT "notifications_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."profiles"
    ADD CONSTRAINT "profiles_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."profiles"
    ADD CONSTRAINT "profiles_username_key" UNIQUE ("username");



ALTER TABLE ONLY "public"."shop_items"
    ADD CONSTRAINT "shop_items_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."tournament_brackets"
    ADD CONSTRAINT "tournament_brackets_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."tournament_registrations"
    ADD CONSTRAINT "tournament_registrations_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."tournament_registrations"
    ADD CONSTRAINT "tournament_registrations_tournament_id_player1_id_player2_i_key" UNIQUE ("tournament_id", "player1_id", "player2_id");



ALTER TABLE ONLY "public"."tournaments"
    ADD CONSTRAINT "tournaments_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."user_achievements"
    ADD CONSTRAINT "user_achievements_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."user_achievements"
    ADD CONSTRAINT "user_achievements_user_id_achievement_id_key" UNIQUE ("user_id", "achievement_id");



ALTER TABLE ONLY "public"."user_inventory"
    ADD CONSTRAINT "user_inventory_pkey" PRIMARY KEY ("id");



ALTER TABLE ONLY "public"."user_inventory"
    ADD CONSTRAINT "user_inventory_user_id_item_id_key" UNIQUE ("user_id", "item_id");



CREATE INDEX "idx_activity_feed_user" ON "public"."activity_feed" USING "btree" ("user_id", "created_at" DESC);



CREATE INDEX "idx_bets_match" ON "public"."bets" USING "btree" ("match_id");



CREATE INDEX "idx_bets_user" ON "public"."bets" USING "btree" ("bettor_id");



CREATE INDEX "idx_elo_history_user" ON "public"."elo_history" USING "btree" ("user_id", "recorded_at" DESC);



CREATE INDEX "idx_fantasy_gameweek_rankings" ON "public"."fantasy_gameweek_rankings" USING "btree" ("gameweek_id", "rank");



CREATE INDEX "idx_fantasy_league_members" ON "public"."fantasy_league_members" USING "btree" ("league_id", "team_id");



CREATE INDEX "idx_fantasy_performances_gameweek" ON "public"."fantasy_player_performances" USING "btree" ("gameweek_id");



CREATE INDEX "idx_fantasy_performances_player" ON "public"."fantasy_player_performances" USING "btree" ("player_id");



CREATE INDEX "idx_fantasy_players_points" ON "public"."fantasy_players" USING "btree" ("total_points" DESC);



CREATE INDEX "idx_fantasy_players_price" ON "public"."fantasy_players" USING "btree" ("current_price" DESC);



CREATE INDEX "idx_fantasy_players_season" ON "public"."fantasy_players" USING "btree" ("season_id", "is_available");



CREATE INDEX "idx_fantasy_teams_rank" ON "public"."fantasy_teams" USING "btree" ("global_rank");



CREATE INDEX "idx_fantasy_teams_season" ON "public"."fantasy_teams" USING "btree" ("season_id");



CREATE INDEX "idx_matches_date" ON "public"."matches" USING "btree" ("match_date" DESC);



CREATE INDEX "idx_matches_players" ON "public"."matches" USING "btree" ("team_a_player1_id", "team_a_player2_id", "team_b_player1_id", "team_b_player2_id");



CREATE INDEX "idx_notifications_user" ON "public"."notifications" USING "btree" ("user_id", "is_read", "created_at" DESC);



CREATE INDEX "idx_profiles_elo" ON "public"."profiles" USING "btree" ("elo_rating" DESC);



CREATE INDEX "idx_profiles_username" ON "public"."profiles" USING "btree" ("username");



CREATE INDEX "idx_tournament_status" ON "public"."tournaments" USING "btree" ("status", "start_date");



CREATE OR REPLACE TRIGGER "prevent_sensitive_data_modification_trigger" BEFORE UPDATE ON "public"."profiles" FOR EACH ROW EXECUTE FUNCTION "public"."prevent_sensitive_data_modification"();



CREATE OR REPLACE TRIGGER "update_profiles_updated_at" BEFORE UPDATE ON "public"."profiles" FOR EACH ROW EXECUTE FUNCTION "public"."update_updated_at_column"();



ALTER TABLE ONLY "public"."activity_feed"
    ADD CONSTRAINT "activity_feed_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."activity_reactions"
    ADD CONSTRAINT "activity_reactions_activity_id_fkey" FOREIGN KEY ("activity_id") REFERENCES "public"."activity_feed"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."activity_reactions"
    ADD CONSTRAINT "activity_reactions_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."bets"
    ADD CONSTRAINT "bets_bettor_id_fkey" FOREIGN KEY ("bettor_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."bets"
    ADD CONSTRAINT "bets_match_id_fkey" FOREIGN KEY ("match_id") REFERENCES "public"."matches"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."championship_rounds"
    ADD CONSTRAINT "championship_rounds_championship_id_fkey" FOREIGN KEY ("championship_id") REFERENCES "public"."championships"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."championship_standings"
    ADD CONSTRAINT "championship_standings_championship_id_fkey" FOREIGN KEY ("championship_id") REFERENCES "public"."championships"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."championship_standings"
    ADD CONSTRAINT "championship_standings_registration_id_fkey" FOREIGN KEY ("registration_id") REFERENCES "public"."tournament_registrations"("id");



ALTER TABLE ONLY "public"."championships"
    ADD CONSTRAINT "championships_created_by_fkey" FOREIGN KEY ("created_by") REFERENCES "public"."profiles"("id");



ALTER TABLE ONLY "public"."championships"
    ADD CONSTRAINT "championships_group_id_fkey" FOREIGN KEY ("group_id") REFERENCES "public"."groups"("id");



ALTER TABLE ONLY "public"."coin_transactions"
    ADD CONSTRAINT "coin_transactions_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."comments"
    ADD CONSTRAINT "comments_activity_id_fkey" FOREIGN KEY ("activity_id") REFERENCES "public"."activity_feed"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."comments"
    ADD CONSTRAINT "comments_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."elo_history"
    ADD CONSTRAINT "elo_history_match_id_fkey" FOREIGN KEY ("match_id") REFERENCES "public"."matches"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."elo_history"
    ADD CONSTRAINT "elo_history_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_gameweek_rankings"
    ADD CONSTRAINT "fantasy_gameweek_rankings_gameweek_id_fkey" FOREIGN KEY ("gameweek_id") REFERENCES "public"."fantasy_gameweeks"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_gameweek_rankings"
    ADD CONSTRAINT "fantasy_gameweek_rankings_team_id_fkey" FOREIGN KEY ("team_id") REFERENCES "public"."fantasy_teams"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_gameweeks"
    ADD CONSTRAINT "fantasy_gameweeks_season_id_fkey" FOREIGN KEY ("season_id") REFERENCES "public"."fantasy_seasons"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_league_members"
    ADD CONSTRAINT "fantasy_league_members_league_id_fkey" FOREIGN KEY ("league_id") REFERENCES "public"."fantasy_leagues"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_league_members"
    ADD CONSTRAINT "fantasy_league_members_team_id_fkey" FOREIGN KEY ("team_id") REFERENCES "public"."fantasy_teams"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_leagues"
    ADD CONSTRAINT "fantasy_leagues_created_by_fkey" FOREIGN KEY ("created_by") REFERENCES "public"."profiles"("id");



ALTER TABLE ONLY "public"."fantasy_leagues"
    ADD CONSTRAINT "fantasy_leagues_season_id_fkey" FOREIGN KEY ("season_id") REFERENCES "public"."fantasy_seasons"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_news"
    ADD CONSTRAINT "fantasy_news_season_id_fkey" FOREIGN KEY ("season_id") REFERENCES "public"."fantasy_seasons"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_player_performances"
    ADD CONSTRAINT "fantasy_player_performances_gameweek_id_fkey" FOREIGN KEY ("gameweek_id") REFERENCES "public"."fantasy_gameweeks"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_player_performances"
    ADD CONSTRAINT "fantasy_player_performances_match_id_fkey" FOREIGN KEY ("match_id") REFERENCES "public"."matches"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_player_performances"
    ADD CONSTRAINT "fantasy_player_performances_player_id_fkey" FOREIGN KEY ("player_id") REFERENCES "public"."fantasy_players"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_player_price_history"
    ADD CONSTRAINT "fantasy_player_price_history_gameweek_id_fkey" FOREIGN KEY ("gameweek_id") REFERENCES "public"."fantasy_gameweeks"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_player_price_history"
    ADD CONSTRAINT "fantasy_player_price_history_player_id_fkey" FOREIGN KEY ("player_id") REFERENCES "public"."fantasy_players"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_players"
    ADD CONSTRAINT "fantasy_players_season_id_fkey" FOREIGN KEY ("season_id") REFERENCES "public"."fantasy_seasons"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_players"
    ADD CONSTRAINT "fantasy_players_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_team_achievements"
    ADD CONSTRAINT "fantasy_team_achievements_achievement_id_fkey" FOREIGN KEY ("achievement_id") REFERENCES "public"."fantasy_achievements"("id");



ALTER TABLE ONLY "public"."fantasy_team_achievements"
    ADD CONSTRAINT "fantasy_team_achievements_team_id_fkey" FOREIGN KEY ("team_id") REFERENCES "public"."fantasy_teams"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_team_chips"
    ADD CONSTRAINT "fantasy_team_chips_chip_id_fkey" FOREIGN KEY ("chip_id") REFERENCES "public"."fantasy_chips"("id");



ALTER TABLE ONLY "public"."fantasy_team_chips"
    ADD CONSTRAINT "fantasy_team_chips_gameweek_id_fkey" FOREIGN KEY ("gameweek_id") REFERENCES "public"."fantasy_gameweeks"("id");



ALTER TABLE ONLY "public"."fantasy_team_chips"
    ADD CONSTRAINT "fantasy_team_chips_team_id_fkey" FOREIGN KEY ("team_id") REFERENCES "public"."fantasy_teams"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_team_rosters"
    ADD CONSTRAINT "fantasy_team_rosters_player_id_fkey" FOREIGN KEY ("player_id") REFERENCES "public"."fantasy_players"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_team_rosters"
    ADD CONSTRAINT "fantasy_team_rosters_team_id_fkey" FOREIGN KEY ("team_id") REFERENCES "public"."fantasy_teams"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_teams"
    ADD CONSTRAINT "fantasy_teams_season_id_fkey" FOREIGN KEY ("season_id") REFERENCES "public"."fantasy_seasons"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_teams"
    ADD CONSTRAINT "fantasy_teams_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_transfers"
    ADD CONSTRAINT "fantasy_transfers_gameweek_id_fkey" FOREIGN KEY ("gameweek_id") REFERENCES "public"."fantasy_gameweeks"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."fantasy_transfers"
    ADD CONSTRAINT "fantasy_transfers_player_in_id_fkey" FOREIGN KEY ("player_in_id") REFERENCES "public"."fantasy_players"("id");



ALTER TABLE ONLY "public"."fantasy_transfers"
    ADD CONSTRAINT "fantasy_transfers_player_out_id_fkey" FOREIGN KEY ("player_out_id") REFERENCES "public"."fantasy_players"("id");



ALTER TABLE ONLY "public"."fantasy_transfers"
    ADD CONSTRAINT "fantasy_transfers_team_id_fkey" FOREIGN KEY ("team_id") REFERENCES "public"."fantasy_teams"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."friendships"
    ADD CONSTRAINT "friendships_friend_id_fkey" FOREIGN KEY ("friend_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."friendships"
    ADD CONSTRAINT "friendships_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."group_members"
    ADD CONSTRAINT "group_members_group_id_fkey" FOREIGN KEY ("group_id") REFERENCES "public"."groups"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."group_members"
    ADD CONSTRAINT "group_members_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."groups"
    ADD CONSTRAINT "groups_created_by_fkey" FOREIGN KEY ("created_by") REFERENCES "public"."profiles"("id");



ALTER TABLE ONLY "public"."matches"
    ADD CONSTRAINT "matches_created_by_fkey" FOREIGN KEY ("created_by") REFERENCES "public"."profiles"("id");



ALTER TABLE ONLY "public"."matches"
    ADD CONSTRAINT "matches_team_a_player1_id_fkey" FOREIGN KEY ("team_a_player1_id") REFERENCES "public"."profiles"("id");



ALTER TABLE ONLY "public"."matches"
    ADD CONSTRAINT "matches_team_a_player2_id_fkey" FOREIGN KEY ("team_a_player2_id") REFERENCES "public"."profiles"("id");



ALTER TABLE ONLY "public"."matches"
    ADD CONSTRAINT "matches_team_b_player1_id_fkey" FOREIGN KEY ("team_b_player1_id") REFERENCES "public"."profiles"("id");



ALTER TABLE ONLY "public"."matches"
    ADD CONSTRAINT "matches_team_b_player2_id_fkey" FOREIGN KEY ("team_b_player2_id") REFERENCES "public"."profiles"("id");



ALTER TABLE ONLY "public"."matches"
    ADD CONSTRAINT "matches_tournament_id_fkey" FOREIGN KEY ("tournament_id") REFERENCES "public"."tournaments"("id") ON DELETE SET NULL;



ALTER TABLE ONLY "public"."notifications"
    ADD CONSTRAINT "notifications_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."profiles"
    ADD CONSTRAINT "profiles_id_fkey" FOREIGN KEY ("id") REFERENCES "auth"."users"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."tournament_brackets"
    ADD CONSTRAINT "tournament_brackets_match_id_fkey" FOREIGN KEY ("match_id") REFERENCES "public"."matches"("id");



ALTER TABLE ONLY "public"."tournament_brackets"
    ADD CONSTRAINT "tournament_brackets_next_bracket_id_fkey" FOREIGN KEY ("next_bracket_id") REFERENCES "public"."tournament_brackets"("id");



ALTER TABLE ONLY "public"."tournament_brackets"
    ADD CONSTRAINT "tournament_brackets_team_a_registration_id_fkey" FOREIGN KEY ("team_a_registration_id") REFERENCES "public"."tournament_registrations"("id");



ALTER TABLE ONLY "public"."tournament_brackets"
    ADD CONSTRAINT "tournament_brackets_team_b_registration_id_fkey" FOREIGN KEY ("team_b_registration_id") REFERENCES "public"."tournament_registrations"("id");



ALTER TABLE ONLY "public"."tournament_brackets"
    ADD CONSTRAINT "tournament_brackets_tournament_id_fkey" FOREIGN KEY ("tournament_id") REFERENCES "public"."tournaments"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."tournament_brackets"
    ADD CONSTRAINT "tournament_brackets_winner_registration_id_fkey" FOREIGN KEY ("winner_registration_id") REFERENCES "public"."tournament_registrations"("id");



ALTER TABLE ONLY "public"."tournament_registrations"
    ADD CONSTRAINT "tournament_registrations_player1_id_fkey" FOREIGN KEY ("player1_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."tournament_registrations"
    ADD CONSTRAINT "tournament_registrations_player2_id_fkey" FOREIGN KEY ("player2_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."tournament_registrations"
    ADD CONSTRAINT "tournament_registrations_tournament_id_fkey" FOREIGN KEY ("tournament_id") REFERENCES "public"."tournaments"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."tournaments"
    ADD CONSTRAINT "tournaments_created_by_fkey" FOREIGN KEY ("created_by") REFERENCES "public"."profiles"("id");



ALTER TABLE ONLY "public"."tournaments"
    ADD CONSTRAINT "tournaments_group_id_fkey" FOREIGN KEY ("group_id") REFERENCES "public"."groups"("id");



ALTER TABLE ONLY "public"."user_achievements"
    ADD CONSTRAINT "user_achievements_achievement_id_fkey" FOREIGN KEY ("achievement_id") REFERENCES "public"."achievements"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."user_achievements"
    ADD CONSTRAINT "user_achievements_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."user_inventory"
    ADD CONSTRAINT "user_inventory_item_id_fkey" FOREIGN KEY ("item_id") REFERENCES "public"."shop_items"("id") ON DELETE CASCADE;



ALTER TABLE ONLY "public"."user_inventory"
    ADD CONSTRAINT "user_inventory_user_id_fkey" FOREIGN KEY ("user_id") REFERENCES "public"."profiles"("id") ON DELETE CASCADE;



CREATE POLICY "Achievements viewable by everyone" ON "public"."achievements" FOR SELECT USING (true);



CREATE POLICY "Active championships viewable by everyone" ON "public"."championships" FOR SELECT USING ((("status")::"text" = ANY ((ARRAY['active'::character varying, 'paused'::character varying, 'completed'::character varying])::"text"[])));



CREATE POLICY "Active tournaments viewable by everyone" ON "public"."tournaments" FOR SELECT USING ((("status")::"text" = ANY ((ARRAY['upcoming'::character varying, 'in_progress'::character varying, 'completed'::character varying])::"text"[])));



CREATE POLICY "Available shop items viewable by everyone" ON "public"."shop_items" FOR SELECT USING (("is_available" = true));



CREATE POLICY "Cannot bet on own matches" ON "public"."bets" FOR INSERT WITH CHECK ((NOT (EXISTS ( SELECT 1
   FROM "public"."matches" "m"
  WHERE (("m"."id" = "bets"."match_id") AND (("m"."team_a_player1_id" = "auth"."uid"()) OR ("m"."team_a_player2_id" = "auth"."uid"()) OR ("m"."team_b_player1_id" = "auth"."uid"()) OR ("m"."team_b_player2_id" = "auth"."uid"())))))));



CREATE POLICY "Championship creators can update" ON "public"."championships" FOR UPDATE USING (("auth"."uid"() = "created_by"));



CREATE POLICY "Championship organizers can manage rounds" ON "public"."championship_rounds" FOR INSERT WITH CHECK ((EXISTS ( SELECT 1
   FROM "public"."championships" "c"
  WHERE (("c"."id" = "championship_rounds"."championship_id") AND ("c"."created_by" = "auth"."uid"())))));



CREATE POLICY "Championship rounds viewable by everyone" ON "public"."championship_rounds" FOR SELECT USING (true);



CREATE POLICY "Championship standings viewable by everyone" ON "public"."championship_standings" FOR SELECT USING (true);



CREATE POLICY "Comments visible with activities" ON "public"."comments" FOR SELECT USING ((EXISTS ( SELECT 1
   FROM "public"."activity_feed" "af"
  WHERE (("af"."id" = "comments"."activity_id") AND ((("af"."visibility")::"text" = 'public'::"text") OR ("af"."user_id" = "auth"."uid"()) OR ((("af"."visibility")::"text" = 'friends'::"text") AND (EXISTS ( SELECT 1
           FROM "public"."friendships"
          WHERE (((("friendships"."user_id" = "auth"."uid"()) AND ("friendships"."friend_id" = "af"."user_id")) OR (("friendships"."friend_id" = "auth"."uid"()) AND ("friendships"."user_id" = "af"."user_id"))) AND (("friendships"."status")::"text" = 'accepted'::"text"))))))))));



CREATE POLICY "Creator can update unvalidated matches" ON "public"."matches" FOR UPDATE USING ((("auth"."uid"() = "created_by") AND ("is_validated" = false)));



CREATE POLICY "Group admins can add members" ON "public"."group_members" FOR INSERT WITH CHECK ((EXISTS ( SELECT 1
   FROM "public"."group_members" "gm"
  WHERE (("gm"."group_id" = "group_members"."group_id") AND ("gm"."user_id" = "auth"."uid"()) AND (("gm"."role")::"text" = 'admin'::"text")))));



CREATE POLICY "Group admins can remove members" ON "public"."group_members" FOR DELETE USING ((EXISTS ( SELECT 1
   FROM "public"."group_members" "gm"
  WHERE (("gm"."group_id" = "group_members"."group_id") AND ("gm"."user_id" = "auth"."uid"()) AND (("gm"."role")::"text" = 'admin'::"text")))));



CREATE POLICY "Group admins can update groups" ON "public"."groups" FOR UPDATE USING ((EXISTS ( SELECT 1
   FROM "public"."group_members"
  WHERE (("group_members"."group_id" = "groups"."id") AND ("group_members"."user_id" = "auth"."uid"()) AND (("group_members"."role")::"text" = 'admin'::"text")))));



CREATE POLICY "Group members visible to group members" ON "public"."group_members" FOR SELECT USING ((EXISTS ( SELECT 1
   FROM "public"."groups" "g"
  WHERE (("g"."id" = "group_members"."group_id") AND (("g"."is_private" = false) OR (EXISTS ( SELECT 1
           FROM "public"."group_members" "gm2"
          WHERE (("gm2"."group_id" = "g"."id") AND ("gm2"."user_id" = "auth"."uid"())))))))));



CREATE POLICY "Matches viewable by everyone" ON "public"."matches" FOR SELECT USING (true);



CREATE POLICY "Participants can create matches" ON "public"."matches" FOR INSERT WITH CHECK ((((("auth"."uid"() = "team_a_player1_id") OR ("auth"."uid"() = "team_a_player2_id")) OR ("auth"."uid"() = "team_b_player1_id")) OR ("auth"."uid"() = "team_b_player2_id")));



CREATE POLICY "Players can register for tournaments" ON "public"."tournament_registrations" FOR INSERT WITH CHECK ((("auth"."uid"() = "player1_id") OR ("auth"."uid"() = "player2_id")));



CREATE POLICY "Private groups viewable by members" ON "public"."groups" FOR SELECT USING ((("is_private" = true) AND (EXISTS ( SELECT 1
   FROM "public"."group_members"
  WHERE (("group_members"."group_id" = "groups"."id") AND ("group_members"."user_id" = "auth"."uid"()))))));



CREATE POLICY "Public groups viewable by everyone" ON "public"."groups" FOR SELECT USING (("is_private" = false));



CREATE POLICY "Public profiles viewable by everyone" ON "public"."profiles" FOR SELECT USING (true);



CREATE POLICY "Reactions visible with activities" ON "public"."activity_reactions" FOR SELECT USING ((EXISTS ( SELECT 1
   FROM "public"."activity_feed" "af"
  WHERE (("af"."id" = "activity_reactions"."activity_id") AND ((("af"."visibility")::"text" = 'public'::"text") OR ("af"."user_id" = "auth"."uid"()) OR ((("af"."visibility")::"text" = 'friends'::"text") AND (EXISTS ( SELECT 1
           FROM "public"."friendships"
          WHERE (((("friendships"."user_id" = "auth"."uid"()) AND ("friendships"."friend_id" = "af"."user_id")) OR (("friendships"."friend_id" = "auth"."uid"()) AND ("friendships"."user_id" = "af"."user_id"))) AND (("friendships"."status")::"text" = 'accepted'::"text"))))))))));



CREATE POLICY "System can create notifications" ON "public"."notifications" FOR INSERT WITH CHECK (true);



CREATE POLICY "System can create transactions" ON "public"."coin_transactions" FOR INSERT WITH CHECK (true);



CREATE POLICY "System can insert ELO history" ON "public"."elo_history" FOR INSERT WITH CHECK (true);



CREATE POLICY "System can unlock achievements" ON "public"."user_achievements" FOR INSERT WITH CHECK (true);



CREATE POLICY "Tournament brackets viewable by everyone" ON "public"."tournament_brackets" FOR SELECT USING (true);



CREATE POLICY "Tournament creators can update" ON "public"."tournaments" FOR UPDATE USING (("auth"."uid"() = "created_by"));



CREATE POLICY "Tournament organizers can manage brackets" ON "public"."tournament_brackets" FOR INSERT WITH CHECK ((EXISTS ( SELECT 1
   FROM "public"."tournaments" "t"
  WHERE (("t"."id" = "tournament_brackets"."tournament_id") AND ("t"."created_by" = "auth"."uid"())))));



CREATE POLICY "Tournament registrations viewable by participants" ON "public"."tournament_registrations" FOR SELECT USING ((("auth"."uid"() = "player1_id") OR ("auth"."uid"() = "player2_id") OR (EXISTS ( SELECT 1
   FROM "public"."tournaments" "t"
  WHERE ("t"."id" = "tournament_registrations"."tournament_id")))));



CREATE POLICY "Users can add comments" ON "public"."comments" FOR INSERT WITH CHECK (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can add reactions" ON "public"."activity_reactions" FOR INSERT WITH CHECK (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can add to own inventory" ON "public"."user_inventory" FOR INSERT WITH CHECK (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can create bets" ON "public"."bets" FOR INSERT WITH CHECK (("auth"."uid"() = "bettor_id"));



CREATE POLICY "Users can create championships" ON "public"."championships" FOR INSERT WITH CHECK (("auth"."uid"() = "created_by"));



CREATE POLICY "Users can create friendships" ON "public"."friendships" FOR INSERT WITH CHECK (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can create groups" ON "public"."groups" FOR INSERT WITH CHECK (("auth"."uid"() = "created_by"));



CREATE POLICY "Users can create own activities" ON "public"."activity_feed" FOR INSERT WITH CHECK (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can create tournaments" ON "public"."tournaments" FOR INSERT WITH CHECK (("auth"."uid"() = "created_by"));



CREATE POLICY "Users can delete own activities" ON "public"."activity_feed" FOR DELETE USING (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can delete own comments" ON "public"."comments" FOR DELETE USING (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can delete own friendships" ON "public"."friendships" FOR DELETE USING (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can delete own reactions" ON "public"."activity_reactions" FOR DELETE USING (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can insert own profile" ON "public"."profiles" FOR INSERT WITH CHECK ((( SELECT "auth"."uid"() AS "uid") = "id"));



CREATE POLICY "Users can update own friendship requests" ON "public"."friendships" FOR UPDATE USING ((("auth"."uid"() = "user_id") OR ("auth"."uid"() = "friend_id")));



CREATE POLICY "Users can update own inventory" ON "public"."user_inventory" FOR UPDATE USING (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can update own notifications" ON "public"."notifications" FOR UPDATE USING (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can update own profile" ON "public"."profiles" FOR UPDATE USING (("auth"."uid"() = "id"));



CREATE POLICY "Users can view friends activities" ON "public"."activity_feed" FOR SELECT USING (((("visibility")::"text" = 'friends'::"text") AND (EXISTS ( SELECT 1
   FROM "public"."friendships"
  WHERE (((("friendships"."user_id" = "auth"."uid"()) AND ("friendships"."friend_id" = "activity_feed"."user_id")) OR (("friendships"."friend_id" = "auth"."uid"()) AND ("friendships"."user_id" = "activity_feed"."user_id"))) AND (("friendships"."status")::"text" = 'accepted'::"text"))))));



CREATE POLICY "Users can view own ELO history" ON "public"."elo_history" FOR SELECT USING (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can view own achievements" ON "public"."user_achievements" FOR SELECT USING (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can view own activities" ON "public"."activity_feed" FOR SELECT USING (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can view own bets" ON "public"."bets" FOR SELECT USING (("auth"."uid"() = "bettor_id"));



CREATE POLICY "Users can view own friendships" ON "public"."friendships" FOR SELECT USING ((("auth"."uid"() = "user_id") OR ("auth"."uid"() = "friend_id")));



CREATE POLICY "Users can view own inventory" ON "public"."user_inventory" FOR SELECT USING (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can view own notifications" ON "public"."notifications" FOR SELECT USING (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can view own transactions" ON "public"."coin_transactions" FOR SELECT USING (("auth"."uid"() = "user_id"));



CREATE POLICY "Users can view public activities" ON "public"."activity_feed" FOR SELECT USING ((("visibility")::"text" = 'public'::"text"));



ALTER TABLE "public"."achievements" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "achievements_admin_only" ON "public"."achievements" USING (false);



CREATE POLICY "achievements_select_public" ON "public"."achievements" FOR SELECT USING (true);



ALTER TABLE "public"."activity_feed" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "activity_feed_delete_own" ON "public"."activity_feed" FOR DELETE USING (("auth"."uid"() = "user_id"));



CREATE POLICY "activity_feed_insert_own" ON "public"."activity_feed" FOR INSERT WITH CHECK (("auth"."uid"() = "user_id"));



CREATE POLICY "activity_feed_select_by_visibility" ON "public"."activity_feed" FOR SELECT USING (((("visibility")::"text" = 'public'::"text") OR ("auth"."uid"() = "user_id") OR ((("visibility")::"text" = 'friends'::"text") AND "public"."are_friends"("auth"."uid"(), "user_id"))));



CREATE POLICY "activity_feed_update_own" ON "public"."activity_feed" FOR UPDATE USING (("auth"."uid"() = "user_id"));



ALTER TABLE "public"."activity_reactions" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "activity_reactions_delete_own" ON "public"."activity_reactions" FOR DELETE USING (("auth"."uid"() = "user_id"));



CREATE POLICY "activity_reactions_insert_on_accessible_activities" ON "public"."activity_reactions" FOR INSERT WITH CHECK ((("auth"."uid"() = "user_id") AND (EXISTS ( SELECT 1
   FROM "public"."activity_feed" "af"
  WHERE (("af"."id" = "activity_reactions"."activity_id") AND ((("af"."visibility")::"text" = 'public'::"text") OR ("af"."user_id" = "auth"."uid"()) OR ((("af"."visibility")::"text" = 'friends'::"text") AND "public"."are_friends"("auth"."uid"(), "af"."user_id"))))))));



CREATE POLICY "activity_reactions_select_with_activities" ON "public"."activity_reactions" FOR SELECT USING ((EXISTS ( SELECT 1
   FROM "public"."activity_feed" "af"
  WHERE (("af"."id" = "activity_reactions"."activity_id") AND ((("af"."visibility")::"text" = 'public'::"text") OR ("af"."user_id" = "auth"."uid"()) OR ((("af"."visibility")::"text" = 'friends'::"text") AND "public"."are_friends"("auth"."uid"(), "af"."user_id")))))));



CREATE POLICY "activity_reactions_update_own" ON "public"."activity_reactions" FOR UPDATE USING (("auth"."uid"() = "user_id"));



ALTER TABLE "public"."bets" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "bets_delete_own_pending" ON "public"."bets" FOR DELETE USING ((("auth"."uid"() = "bettor_id") AND (("status")::"text" = 'pending'::"text")));



CREATE POLICY "bets_insert_own_with_validation" ON "public"."bets" FOR INSERT WITH CHECK ((("auth"."uid"() = "bettor_id") AND "public"."can_bet_on_match"("auth"."uid"(), "match_id") AND "public"."has_sufficient_coins"("auth"."uid"(), "bet_amount")));



CREATE POLICY "bets_select_own" ON "public"."bets" FOR SELECT USING (("auth"."uid"() = "bettor_id"));



CREATE POLICY "bets_update_own_pending" ON "public"."bets" FOR UPDATE USING ((("auth"."uid"() = "bettor_id") AND (("status")::"text" = 'pending'::"text")));



ALTER TABLE "public"."championship_rounds" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "championship_rounds_insert_organizers" ON "public"."championship_rounds" FOR INSERT WITH CHECK ((EXISTS ( SELECT 1
   FROM "public"."championships" "c"
  WHERE (("c"."id" = "championship_rounds"."championship_id") AND (("c"."created_by" = "auth"."uid"()) OR (("c"."group_id" IS NOT NULL) AND "public"."is_group_admin"("auth"."uid"(), "c"."group_id")))))));



CREATE POLICY "championship_rounds_modify_organizers" ON "public"."championship_rounds" USING ((EXISTS ( SELECT 1
   FROM "public"."championships" "c"
  WHERE (("c"."id" = "championship_rounds"."championship_id") AND (("c"."created_by" = "auth"."uid"()) OR (("c"."group_id" IS NOT NULL) AND "public"."is_group_admin"("auth"."uid"(), "c"."group_id")))))));



CREATE POLICY "championship_rounds_select_public" ON "public"."championship_rounds" FOR SELECT USING (true);



ALTER TABLE "public"."championship_standings" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "championship_standings_select_public" ON "public"."championship_standings" FOR SELECT USING (true);



CREATE POLICY "championship_standings_system_only" ON "public"."championship_standings" USING (false);



ALTER TABLE "public"."championships" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "championships_delete_creator_or_group_admin" ON "public"."championships" FOR DELETE USING ((("auth"."uid"() = "created_by") OR (("group_id" IS NOT NULL) AND "public"."is_group_admin"("auth"."uid"(), "group_id"))));



CREATE POLICY "championships_insert_own" ON "public"."championships" FOR INSERT WITH CHECK (("auth"."uid"() = "created_by"));



CREATE POLICY "championships_select_active" ON "public"."championships" FOR SELECT USING ((("status")::"text" = ANY ((ARRAY['active'::character varying, 'paused'::character varying, 'completed'::character varying])::"text"[])));



CREATE POLICY "championships_update_creator_or_group_admin" ON "public"."championships" FOR UPDATE USING ((("auth"."uid"() = "created_by") OR (("group_id" IS NOT NULL) AND "public"."is_group_admin"("auth"."uid"(), "group_id"))));



ALTER TABLE "public"."coin_transactions" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "coin_transactions_insert_system" ON "public"."coin_transactions" FOR INSERT WITH CHECK (true);



CREATE POLICY "coin_transactions_no_user_modifications" ON "public"."coin_transactions" USING (false);



CREATE POLICY "coin_transactions_select_own" ON "public"."coin_transactions" FOR SELECT USING (("auth"."uid"() = "user_id"));



ALTER TABLE "public"."comments" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "comments_delete_own" ON "public"."comments" FOR DELETE USING (("auth"."uid"() = "user_id"));



CREATE POLICY "comments_insert_on_accessible_activities" ON "public"."comments" FOR INSERT WITH CHECK ((("auth"."uid"() = "user_id") AND (EXISTS ( SELECT 1
   FROM "public"."activity_feed" "af"
  WHERE (("af"."id" = "comments"."activity_id") AND ((("af"."visibility")::"text" = 'public'::"text") OR ("af"."user_id" = "auth"."uid"()) OR ((("af"."visibility")::"text" = 'friends'::"text") AND "public"."are_friends"("auth"."uid"(), "af"."user_id"))))))));



CREATE POLICY "comments_select_with_activities" ON "public"."comments" FOR SELECT USING ((EXISTS ( SELECT 1
   FROM "public"."activity_feed" "af"
  WHERE (("af"."id" = "comments"."activity_id") AND ((("af"."visibility")::"text" = 'public'::"text") OR ("af"."user_id" = "auth"."uid"()) OR ((("af"."visibility")::"text" = 'friends'::"text") AND "public"."are_friends"("auth"."uid"(), "af"."user_id")))))));



CREATE POLICY "comments_update_own" ON "public"."comments" FOR UPDATE USING (("auth"."uid"() = "user_id"));



ALTER TABLE "public"."elo_history" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "elo_history_insert_system" ON "public"."elo_history" FOR INSERT WITH CHECK (true);



CREATE POLICY "elo_history_no_user_modifications" ON "public"."elo_history" USING (false);



CREATE POLICY "elo_history_select_own" ON "public"."elo_history" FOR SELECT USING (("auth"."uid"() = "user_id"));



ALTER TABLE "public"."fantasy_achievements" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_achievements_admin_only" ON "public"."fantasy_achievements" USING (false);



CREATE POLICY "fantasy_achievements_select_public" ON "public"."fantasy_achievements" FOR SELECT USING (true);



ALTER TABLE "public"."fantasy_chips" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_chips_admin_only" ON "public"."fantasy_chips" USING (false);



CREATE POLICY "fantasy_chips_select_public" ON "public"."fantasy_chips" FOR SELECT USING (true);



ALTER TABLE "public"."fantasy_gameweek_rankings" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_gameweek_rankings_select_public" ON "public"."fantasy_gameweek_rankings" FOR SELECT USING (true);



CREATE POLICY "fantasy_gameweek_rankings_system_only" ON "public"."fantasy_gameweek_rankings" USING (false);



ALTER TABLE "public"."fantasy_gameweeks" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_gameweeks_admin_only" ON "public"."fantasy_gameweeks" USING (false);



CREATE POLICY "fantasy_gameweeks_select_active" ON "public"."fantasy_gameweeks" FOR SELECT USING ((EXISTS ( SELECT 1
   FROM "public"."fantasy_seasons"
  WHERE (("fantasy_seasons"."id" = "fantasy_gameweeks"."season_id") AND (("fantasy_seasons"."status")::"text" = ANY ((ARRAY['upcoming'::character varying, 'active'::character varying, 'completed'::character varying])::"text"[]))))));



ALTER TABLE "public"."fantasy_league_members" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_league_members_delete_admins_or_self" ON "public"."fantasy_league_members" FOR DELETE USING (("public"."is_fantasy_league_admin"("auth"."uid"(), "league_id") OR (EXISTS ( SELECT 1
   FROM "public"."fantasy_teams" "ft"
  WHERE (("ft"."id" = "fantasy_league_members"."team_id") AND ("ft"."user_id" = "auth"."uid"()))))));



CREATE POLICY "fantasy_league_members_insert_join" ON "public"."fantasy_league_members" FOR INSERT WITH CHECK ((EXISTS ( SELECT 1
   FROM "public"."fantasy_teams" "ft"
  WHERE (("ft"."id" = "fantasy_league_members"."team_id") AND ("ft"."user_id" = "auth"."uid"())))));



CREATE POLICY "fantasy_league_members_select_by_league_visibility" ON "public"."fantasy_league_members" FOR SELECT USING ((EXISTS ( SELECT 1
   FROM "public"."fantasy_leagues" "fl"
  WHERE (("fl"."id" = "fantasy_league_members"."league_id") AND ((("fl"."type")::"text" = 'public'::"text") OR "public"."is_fantasy_league_member"("auth"."uid"(), "fl"."id"))))));



CREATE POLICY "fantasy_league_members_update_admins" ON "public"."fantasy_league_members" FOR UPDATE USING ("public"."is_fantasy_league_admin"("auth"."uid"(), "league_id"));



ALTER TABLE "public"."fantasy_leagues" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_leagues_delete_creator_or_admin" ON "public"."fantasy_leagues" FOR DELETE USING ((("auth"."uid"() = "created_by") OR "public"."is_fantasy_league_admin"("auth"."uid"(), "id")));



CREATE POLICY "fantasy_leagues_insert_own" ON "public"."fantasy_leagues" FOR INSERT WITH CHECK (("auth"."uid"() = "created_by"));



CREATE POLICY "fantasy_leagues_select_by_type" ON "public"."fantasy_leagues" FOR SELECT USING (((("type")::"text" = 'public'::"text") OR "public"."is_fantasy_league_member"("auth"."uid"(), "id")));



CREATE POLICY "fantasy_leagues_update_creator_or_admin" ON "public"."fantasy_leagues" FOR UPDATE USING ((("auth"."uid"() = "created_by") OR "public"."is_fantasy_league_admin"("auth"."uid"(), "id")));



ALTER TABLE "public"."fantasy_news" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_news_admin_only" ON "public"."fantasy_news" USING (false);



CREATE POLICY "fantasy_news_select_public" ON "public"."fantasy_news" FOR SELECT USING (true);



ALTER TABLE "public"."fantasy_player_performances" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_player_performances_select_public" ON "public"."fantasy_player_performances" FOR SELECT USING (true);



CREATE POLICY "fantasy_player_performances_system_only" ON "public"."fantasy_player_performances" USING (false);



ALTER TABLE "public"."fantasy_player_price_history" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_player_price_history_select_public" ON "public"."fantasy_player_price_history" FOR SELECT USING (true);



CREATE POLICY "fantasy_player_price_history_system_only" ON "public"."fantasy_player_price_history" USING (false);



ALTER TABLE "public"."fantasy_players" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_players_admin_only" ON "public"."fantasy_players" USING (false);



CREATE POLICY "fantasy_players_select_available" ON "public"."fantasy_players" FOR SELECT USING (("is_available" = true));



ALTER TABLE "public"."fantasy_seasons" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_seasons_admin_only" ON "public"."fantasy_seasons" USING (false);



CREATE POLICY "fantasy_seasons_select_active" ON "public"."fantasy_seasons" FOR SELECT USING ((("status")::"text" = ANY ((ARRAY['upcoming'::character varying, 'active'::character varying, 'completed'::character varying])::"text"[])));



ALTER TABLE "public"."fantasy_team_achievements" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_team_achievements_select_own" ON "public"."fantasy_team_achievements" FOR SELECT USING ("public"."owns_fantasy_team"("auth"."uid"(), "team_id"));



CREATE POLICY "fantasy_team_achievements_system_only" ON "public"."fantasy_team_achievements" USING (false);



ALTER TABLE "public"."fantasy_team_chips" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_team_chips_insert_own" ON "public"."fantasy_team_chips" FOR INSERT WITH CHECK ("public"."owns_fantasy_team"("auth"."uid"(), "team_id"));



CREATE POLICY "fantasy_team_chips_no_modifications" ON "public"."fantasy_team_chips" USING (false);



CREATE POLICY "fantasy_team_chips_select_own" ON "public"."fantasy_team_chips" FOR SELECT USING ("public"."owns_fantasy_team"("auth"."uid"(), "team_id"));



ALTER TABLE "public"."fantasy_team_rosters" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_team_rosters_delete_own" ON "public"."fantasy_team_rosters" FOR DELETE USING ("public"."owns_fantasy_team"("auth"."uid"(), "team_id"));



CREATE POLICY "fantasy_team_rosters_insert_own" ON "public"."fantasy_team_rosters" FOR INSERT WITH CHECK ("public"."owns_fantasy_team"("auth"."uid"(), "team_id"));



CREATE POLICY "fantasy_team_rosters_select_own" ON "public"."fantasy_team_rosters" FOR SELECT USING ("public"."owns_fantasy_team"("auth"."uid"(), "team_id"));



CREATE POLICY "fantasy_team_rosters_update_own" ON "public"."fantasy_team_rosters" FOR UPDATE USING ("public"."owns_fantasy_team"("auth"."uid"(), "team_id"));



ALTER TABLE "public"."fantasy_teams" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_teams_delete_own" ON "public"."fantasy_teams" FOR DELETE USING (("auth"."uid"() = "user_id"));



CREATE POLICY "fantasy_teams_insert_own" ON "public"."fantasy_teams" FOR INSERT WITH CHECK ((("auth"."uid"() = "user_id") AND (NOT (EXISTS ( SELECT 1
   FROM "public"."fantasy_teams" "ft2"
  WHERE (("ft2"."season_id" = "fantasy_teams"."season_id") AND ("ft2"."user_id" = "auth"."uid"())))))));



CREATE POLICY "fantasy_teams_select_public" ON "public"."fantasy_teams" FOR SELECT USING (true);



CREATE POLICY "fantasy_teams_update_own" ON "public"."fantasy_teams" FOR UPDATE USING (("auth"."uid"() = "user_id"));



ALTER TABLE "public"."fantasy_transfers" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "fantasy_transfers_insert_own" ON "public"."fantasy_transfers" FOR INSERT WITH CHECK ("public"."owns_fantasy_team"("auth"."uid"(), "team_id"));



CREATE POLICY "fantasy_transfers_no_modifications" ON "public"."fantasy_transfers" USING (false);



CREATE POLICY "fantasy_transfers_select_own" ON "public"."fantasy_transfers" FOR SELECT USING ("public"."owns_fantasy_team"("auth"."uid"(), "team_id"));



ALTER TABLE "public"."friendships" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "friendships_delete_own" ON "public"."friendships" FOR DELETE USING (("auth"."uid"() = "user_id"));



CREATE POLICY "friendships_insert_own" ON "public"."friendships" FOR INSERT WITH CHECK ((("auth"."uid"() = "user_id") AND ("auth"."uid"() <> "friend_id") AND (NOT (EXISTS ( SELECT 1
   FROM "public"."friendships" "f2"
  WHERE ((("f2"."user_id" = "auth"."uid"()) AND ("f2"."friend_id" = "friendships"."friend_id")) OR (("f2"."friend_id" = "auth"."uid"()) AND ("f2"."user_id" = "friendships"."friend_id"))))))));



CREATE POLICY "friendships_select_own" ON "public"."friendships" FOR SELECT USING ((("auth"."uid"() = "user_id") OR ("auth"."uid"() = "friend_id")));



CREATE POLICY "friendships_update_participants" ON "public"."friendships" FOR UPDATE USING ((("auth"."uid"() = "user_id") OR ("auth"."uid"() = "friend_id")));



ALTER TABLE "public"."group_members" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "group_members_delete_admins_or_self" ON "public"."group_members" FOR DELETE USING (((EXISTS ( SELECT 1
   FROM "public"."group_members" "gm"
  WHERE (("gm"."group_id" = "group_members"."group_id") AND ("gm"."user_id" = "auth"."uid"()) AND (("gm"."role")::"text" = 'admin'::"text")))) OR ("auth"."uid"() = "user_id")));



CREATE POLICY "group_members_insert_admins_or_self" ON "public"."group_members" FOR INSERT WITH CHECK (((EXISTS ( SELECT 1
   FROM "public"."group_members" "gm"
  WHERE (("gm"."group_id" = "group_members"."group_id") AND ("gm"."user_id" = "auth"."uid"()) AND (("gm"."role")::"text" = 'admin'::"text")))) OR ("auth"."uid"() = "user_id")));



CREATE POLICY "group_members_select_by_group_visibility" ON "public"."group_members" FOR SELECT USING ((EXISTS ( SELECT 1
   FROM "public"."groups" "g"
  WHERE (("g"."id" = "group_members"."group_id") AND (("g"."is_private" = false) OR (EXISTS ( SELECT 1
           FROM "public"."group_members" "gm2"
          WHERE (("gm2"."group_id" = "g"."id") AND ("gm2"."user_id" = "auth"."uid"())))))))));



CREATE POLICY "group_members_update_admins" ON "public"."group_members" FOR UPDATE USING ((EXISTS ( SELECT 1
   FROM "public"."group_members" "gm"
  WHERE (("gm"."group_id" = "group_members"."group_id") AND ("gm"."user_id" = "auth"."uid"()) AND (("gm"."role")::"text" = 'admin'::"text")))));



ALTER TABLE "public"."groups" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "groups_delete_admins" ON "public"."groups" FOR DELETE USING ((EXISTS ( SELECT 1
   FROM "public"."group_members"
  WHERE (("group_members"."group_id" = "groups"."id") AND ("group_members"."user_id" = "auth"."uid"()) AND (("group_members"."role")::"text" = 'admin'::"text")))));



CREATE POLICY "groups_insert_own" ON "public"."groups" FOR INSERT WITH CHECK (("auth"."uid"() = "created_by"));



CREATE POLICY "groups_select_visibility" ON "public"."groups" FOR SELECT USING ((("is_private" = false) OR (EXISTS ( SELECT 1
   FROM "public"."group_members"
  WHERE (("group_members"."group_id" = "groups"."id") AND ("group_members"."user_id" = "auth"."uid"()))))));



CREATE POLICY "groups_update_admins" ON "public"."groups" FOR UPDATE USING ((EXISTS ( SELECT 1
   FROM "public"."group_members"
  WHERE (("group_members"."group_id" = "groups"."id") AND ("group_members"."user_id" = "auth"."uid"()) AND (("group_members"."role")::"text" = 'admin'::"text")))));



ALTER TABLE "public"."matches" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "matches_delete_participants_unvalidated" ON "public"."matches" FOR DELETE USING (((((("auth"."uid"() = "team_a_player1_id") OR ("auth"."uid"() = "team_a_player2_id")) OR ("auth"."uid"() = "team_b_player1_id")) OR ("auth"."uid"() = "team_b_player2_id")) AND ("is_validated" = false)));



CREATE POLICY "matches_insert_participants" ON "public"."matches" FOR INSERT WITH CHECK (((((("auth"."uid"() = "team_a_player1_id") OR ("auth"."uid"() = "team_a_player2_id")) OR ("auth"."uid"() = "team_b_player1_id")) OR ("auth"."uid"() = "team_b_player2_id")) AND ("team_a_player1_id" <> "team_a_player2_id") AND ("team_b_player1_id" <> "team_b_player2_id") AND ("team_a_player1_id" <> "team_b_player1_id") AND ("team_a_player1_id" <> "team_b_player2_id") AND ("team_a_player2_id" <> "team_b_player1_id") AND ("team_a_player2_id" <> "team_b_player2_id")));



CREATE POLICY "matches_select_public" ON "public"."matches" FOR SELECT USING (true);



CREATE POLICY "matches_update_participants_unvalidated" ON "public"."matches" FOR UPDATE USING (((((("auth"."uid"() = "team_a_player1_id") OR ("auth"."uid"() = "team_a_player2_id")) OR ("auth"."uid"() = "team_b_player1_id")) OR ("auth"."uid"() = "team_b_player2_id")) AND ("is_validated" = false)));



ALTER TABLE "public"."notifications" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "notifications_delete_own" ON "public"."notifications" FOR DELETE USING (("auth"."uid"() = "user_id"));



CREATE POLICY "notifications_insert_system" ON "public"."notifications" FOR INSERT WITH CHECK (true);



CREATE POLICY "notifications_select_own" ON "public"."notifications" FOR SELECT USING (("auth"."uid"() = "user_id"));



CREATE POLICY "notifications_update_own" ON "public"."notifications" FOR UPDATE USING (("auth"."uid"() = "user_id"));



ALTER TABLE "public"."profiles" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "profiles_insert_own" ON "public"."profiles" FOR INSERT WITH CHECK (("auth"."uid"() = "id"));



CREATE POLICY "profiles_select_public" ON "public"."profiles" FOR SELECT USING (true);



CREATE POLICY "profiles_update_own_safe_fields" ON "public"."profiles" FOR UPDATE USING (("auth"."uid"() = "id"));



ALTER TABLE "public"."shop_items" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "shop_items_admin_only" ON "public"."shop_items" USING (false);



CREATE POLICY "shop_items_select_available" ON "public"."shop_items" FOR SELECT USING (("is_available" = true));



ALTER TABLE "public"."tournament_brackets" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "tournament_brackets_insert_organizers" ON "public"."tournament_brackets" FOR INSERT WITH CHECK ((EXISTS ( SELECT 1
   FROM "public"."tournaments" "t"
  WHERE (("t"."id" = "tournament_brackets"."tournament_id") AND (("t"."created_by" = "auth"."uid"()) OR (("t"."group_id" IS NOT NULL) AND "public"."is_group_admin"("auth"."uid"(), "t"."group_id")))))));



CREATE POLICY "tournament_brackets_modify_organizers" ON "public"."tournament_brackets" USING ((EXISTS ( SELECT 1
   FROM "public"."tournaments" "t"
  WHERE (("t"."id" = "tournament_brackets"."tournament_id") AND (("t"."created_by" = "auth"."uid"()) OR (("t"."group_id" IS NOT NULL) AND "public"."is_group_admin"("auth"."uid"(), "t"."group_id")))))));



CREATE POLICY "tournament_brackets_select_public" ON "public"."tournament_brackets" FOR SELECT USING (true);



ALTER TABLE "public"."tournament_registrations" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "tournament_registrations_delete_participants_and_organizers" ON "public"."tournament_registrations" FOR DELETE USING ((("auth"."uid"() = "player1_id") OR ("auth"."uid"() = "player2_id") OR (EXISTS ( SELECT 1
   FROM "public"."tournaments" "t"
  WHERE (("t"."id" = "tournament_registrations"."tournament_id") AND (("t"."created_by" = "auth"."uid"()) OR (("t"."group_id" IS NOT NULL) AND "public"."is_group_admin"("auth"."uid"(), "t"."group_id"))))))));



CREATE POLICY "tournament_registrations_insert_participants" ON "public"."tournament_registrations" FOR INSERT WITH CHECK ((("auth"."uid"() = "player1_id") OR ("auth"."uid"() = "player2_id")));



CREATE POLICY "tournament_registrations_select_participants_and_organizers" ON "public"."tournament_registrations" FOR SELECT USING ((("auth"."uid"() = "player1_id") OR ("auth"."uid"() = "player2_id") OR (EXISTS ( SELECT 1
   FROM "public"."tournaments" "t"
  WHERE (("t"."id" = "tournament_registrations"."tournament_id") AND (("t"."created_by" = "auth"."uid"()) OR (("t"."group_id" IS NOT NULL) AND "public"."is_group_admin"("auth"."uid"(), "t"."group_id"))))))));



CREATE POLICY "tournament_registrations_update_participants_and_organizers" ON "public"."tournament_registrations" FOR UPDATE USING ((("auth"."uid"() = "player1_id") OR ("auth"."uid"() = "player2_id") OR (EXISTS ( SELECT 1
   FROM "public"."tournaments" "t"
  WHERE (("t"."id" = "tournament_registrations"."tournament_id") AND (("t"."created_by" = "auth"."uid"()) OR (("t"."group_id" IS NOT NULL) AND "public"."is_group_admin"("auth"."uid"(), "t"."group_id"))))))));



ALTER TABLE "public"."tournaments" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "tournaments_delete_creator_or_group_admin" ON "public"."tournaments" FOR DELETE USING ((("auth"."uid"() = "created_by") OR (("group_id" IS NOT NULL) AND "public"."is_group_admin"("auth"."uid"(), "group_id"))));



CREATE POLICY "tournaments_insert_own" ON "public"."tournaments" FOR INSERT WITH CHECK (("auth"."uid"() = "created_by"));



CREATE POLICY "tournaments_select_active" ON "public"."tournaments" FOR SELECT USING ((("status")::"text" = ANY ((ARRAY['upcoming'::character varying, 'in_progress'::character varying, 'completed'::character varying])::"text"[])));



CREATE POLICY "tournaments_update_creator_or_group_admin" ON "public"."tournaments" FOR UPDATE USING ((("auth"."uid"() = "created_by") OR (("group_id" IS NOT NULL) AND "public"."is_group_admin"("auth"."uid"(), "group_id"))));



ALTER TABLE "public"."user_achievements" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "user_achievements_insert_system" ON "public"."user_achievements" FOR INSERT WITH CHECK (true);



CREATE POLICY "user_achievements_no_user_modifications" ON "public"."user_achievements" USING (false);



CREATE POLICY "user_achievements_select_own" ON "public"."user_achievements" FOR SELECT USING (("auth"."uid"() = "user_id"));



ALTER TABLE "public"."user_inventory" ENABLE ROW LEVEL SECURITY;


CREATE POLICY "user_inventory_insert_own" ON "public"."user_inventory" FOR INSERT WITH CHECK (("auth"."uid"() = "user_id"));



CREATE POLICY "user_inventory_no_delete" ON "public"."user_inventory" FOR DELETE USING (false);



CREATE POLICY "user_inventory_select_own" ON "public"."user_inventory" FOR SELECT USING (("auth"."uid"() = "user_id"));



CREATE POLICY "user_inventory_update_own" ON "public"."user_inventory" FOR UPDATE USING (("auth"."uid"() = "user_id"));





ALTER PUBLICATION "supabase_realtime" OWNER TO "postgres";


GRANT USAGE ON SCHEMA "public" TO "postgres";
GRANT USAGE ON SCHEMA "public" TO "anon";
GRANT USAGE ON SCHEMA "public" TO "authenticated";
GRANT USAGE ON SCHEMA "public" TO "service_role";

























































































































































GRANT ALL ON FUNCTION "public"."are_friends"("p_user1_id" "uuid", "p_user2_id" "uuid") TO "anon";
GRANT ALL ON FUNCTION "public"."are_friends"("p_user1_id" "uuid", "p_user2_id" "uuid") TO "authenticated";
GRANT ALL ON FUNCTION "public"."are_friends"("p_user1_id" "uuid", "p_user2_id" "uuid") TO "service_role";



GRANT ALL ON FUNCTION "public"."can_bet_on_match"("p_user_id" "uuid", "p_match_id" "uuid") TO "anon";
GRANT ALL ON FUNCTION "public"."can_bet_on_match"("p_user_id" "uuid", "p_match_id" "uuid") TO "authenticated";
GRANT ALL ON FUNCTION "public"."can_bet_on_match"("p_user_id" "uuid", "p_match_id" "uuid") TO "service_role";



GRANT ALL ON FUNCTION "public"."has_sufficient_coins"("p_user_id" "uuid", "p_amount" integer) TO "anon";
GRANT ALL ON FUNCTION "public"."has_sufficient_coins"("p_user_id" "uuid", "p_amount" integer) TO "authenticated";
GRANT ALL ON FUNCTION "public"."has_sufficient_coins"("p_user_id" "uuid", "p_amount" integer) TO "service_role";



GRANT ALL ON FUNCTION "public"."is_fantasy_league_admin"("p_user_id" "uuid", "p_league_id" "uuid") TO "anon";
GRANT ALL ON FUNCTION "public"."is_fantasy_league_admin"("p_user_id" "uuid", "p_league_id" "uuid") TO "authenticated";
GRANT ALL ON FUNCTION "public"."is_fantasy_league_admin"("p_user_id" "uuid", "p_league_id" "uuid") TO "service_role";



GRANT ALL ON FUNCTION "public"."is_fantasy_league_member"("p_user_id" "uuid", "p_league_id" "uuid") TO "anon";
GRANT ALL ON FUNCTION "public"."is_fantasy_league_member"("p_user_id" "uuid", "p_league_id" "uuid") TO "authenticated";
GRANT ALL ON FUNCTION "public"."is_fantasy_league_member"("p_user_id" "uuid", "p_league_id" "uuid") TO "service_role";



GRANT ALL ON FUNCTION "public"."is_group_admin"("p_user_id" "uuid", "p_group_id" "uuid") TO "anon";
GRANT ALL ON FUNCTION "public"."is_group_admin"("p_user_id" "uuid", "p_group_id" "uuid") TO "authenticated";
GRANT ALL ON FUNCTION "public"."is_group_admin"("p_user_id" "uuid", "p_group_id" "uuid") TO "service_role";



GRANT ALL ON FUNCTION "public"."is_match_participant"("p_user_id" "uuid", "p_match_id" "uuid") TO "anon";
GRANT ALL ON FUNCTION "public"."is_match_participant"("p_user_id" "uuid", "p_match_id" "uuid") TO "authenticated";
GRANT ALL ON FUNCTION "public"."is_match_participant"("p_user_id" "uuid", "p_match_id" "uuid") TO "service_role";



GRANT ALL ON FUNCTION "public"."is_tournament_participant"("p_user_id" "uuid", "p_tournament_id" "uuid") TO "anon";
GRANT ALL ON FUNCTION "public"."is_tournament_participant"("p_user_id" "uuid", "p_tournament_id" "uuid") TO "authenticated";
GRANT ALL ON FUNCTION "public"."is_tournament_participant"("p_user_id" "uuid", "p_tournament_id" "uuid") TO "service_role";



GRANT ALL ON FUNCTION "public"."owns_fantasy_team"("p_user_id" "uuid", "p_team_id" "uuid") TO "anon";
GRANT ALL ON FUNCTION "public"."owns_fantasy_team"("p_user_id" "uuid", "p_team_id" "uuid") TO "authenticated";
GRANT ALL ON FUNCTION "public"."owns_fantasy_team"("p_user_id" "uuid", "p_team_id" "uuid") TO "service_role";



GRANT ALL ON FUNCTION "public"."prevent_sensitive_data_modification"() TO "anon";
GRANT ALL ON FUNCTION "public"."prevent_sensitive_data_modification"() TO "authenticated";
GRANT ALL ON FUNCTION "public"."prevent_sensitive_data_modification"() TO "service_role";



GRANT ALL ON FUNCTION "public"."update_fantasy_rankings"() TO "anon";
GRANT ALL ON FUNCTION "public"."update_fantasy_rankings"() TO "authenticated";
GRANT ALL ON FUNCTION "public"."update_fantasy_rankings"() TO "service_role";



GRANT ALL ON FUNCTION "public"."update_updated_at_column"() TO "anon";
GRANT ALL ON FUNCTION "public"."update_updated_at_column"() TO "authenticated";
GRANT ALL ON FUNCTION "public"."update_updated_at_column"() TO "service_role";


















GRANT ALL ON TABLE "public"."achievements" TO "anon";
GRANT ALL ON TABLE "public"."achievements" TO "authenticated";
GRANT ALL ON TABLE "public"."achievements" TO "service_role";



GRANT ALL ON TABLE "public"."activity_feed" TO "anon";
GRANT ALL ON TABLE "public"."activity_feed" TO "authenticated";
GRANT ALL ON TABLE "public"."activity_feed" TO "service_role";



GRANT ALL ON TABLE "public"."activity_reactions" TO "anon";
GRANT ALL ON TABLE "public"."activity_reactions" TO "authenticated";
GRANT ALL ON TABLE "public"."activity_reactions" TO "service_role";



GRANT ALL ON TABLE "public"."bets" TO "anon";
GRANT ALL ON TABLE "public"."bets" TO "authenticated";
GRANT ALL ON TABLE "public"."bets" TO "service_role";



GRANT ALL ON TABLE "public"."championship_rounds" TO "anon";
GRANT ALL ON TABLE "public"."championship_rounds" TO "authenticated";
GRANT ALL ON TABLE "public"."championship_rounds" TO "service_role";



GRANT ALL ON TABLE "public"."championship_standings" TO "anon";
GRANT ALL ON TABLE "public"."championship_standings" TO "authenticated";
GRANT ALL ON TABLE "public"."championship_standings" TO "service_role";



GRANT ALL ON TABLE "public"."championships" TO "anon";
GRANT ALL ON TABLE "public"."championships" TO "authenticated";
GRANT ALL ON TABLE "public"."championships" TO "service_role";



GRANT ALL ON TABLE "public"."coin_transactions" TO "anon";
GRANT ALL ON TABLE "public"."coin_transactions" TO "authenticated";
GRANT ALL ON TABLE "public"."coin_transactions" TO "service_role";



GRANT ALL ON TABLE "public"."comments" TO "anon";
GRANT ALL ON TABLE "public"."comments" TO "authenticated";
GRANT ALL ON TABLE "public"."comments" TO "service_role";



GRANT ALL ON TABLE "public"."elo_history" TO "anon";
GRANT ALL ON TABLE "public"."elo_history" TO "authenticated";
GRANT ALL ON TABLE "public"."elo_history" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_achievements" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_achievements" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_achievements" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_chips" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_chips" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_chips" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_gameweek_rankings" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_gameweek_rankings" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_gameweek_rankings" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_gameweeks" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_gameweeks" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_gameweeks" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_league_members" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_league_members" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_league_members" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_leagues" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_leagues" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_leagues" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_news" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_news" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_news" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_player_performances" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_player_performances" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_player_performances" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_player_price_history" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_player_price_history" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_player_price_history" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_players" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_players" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_players" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_seasons" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_seasons" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_seasons" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_team_achievements" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_team_achievements" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_team_achievements" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_team_chips" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_team_chips" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_team_chips" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_team_rosters" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_team_rosters" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_team_rosters" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_teams" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_teams" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_teams" TO "service_role";



GRANT ALL ON TABLE "public"."fantasy_transfers" TO "anon";
GRANT ALL ON TABLE "public"."fantasy_transfers" TO "authenticated";
GRANT ALL ON TABLE "public"."fantasy_transfers" TO "service_role";



GRANT ALL ON TABLE "public"."friendships" TO "anon";
GRANT ALL ON TABLE "public"."friendships" TO "authenticated";
GRANT ALL ON TABLE "public"."friendships" TO "service_role";



GRANT ALL ON TABLE "public"."group_members" TO "anon";
GRANT ALL ON TABLE "public"."group_members" TO "authenticated";
GRANT ALL ON TABLE "public"."group_members" TO "service_role";



GRANT ALL ON TABLE "public"."groups" TO "anon";
GRANT ALL ON TABLE "public"."groups" TO "authenticated";
GRANT ALL ON TABLE "public"."groups" TO "service_role";



GRANT ALL ON TABLE "public"."matches" TO "anon";
GRANT ALL ON TABLE "public"."matches" TO "authenticated";
GRANT ALL ON TABLE "public"."matches" TO "service_role";



GRANT ALL ON TABLE "public"."notifications" TO "anon";
GRANT ALL ON TABLE "public"."notifications" TO "authenticated";
GRANT ALL ON TABLE "public"."notifications" TO "service_role";



GRANT ALL ON TABLE "public"."profiles" TO "anon";
GRANT ALL ON TABLE "public"."profiles" TO "authenticated";
GRANT ALL ON TABLE "public"."profiles" TO "service_role";



GRANT ALL ON TABLE "public"."shop_items" TO "anon";
GRANT ALL ON TABLE "public"."shop_items" TO "authenticated";
GRANT ALL ON TABLE "public"."shop_items" TO "service_role";



GRANT ALL ON TABLE "public"."tournament_brackets" TO "anon";
GRANT ALL ON TABLE "public"."tournament_brackets" TO "authenticated";
GRANT ALL ON TABLE "public"."tournament_brackets" TO "service_role";



GRANT ALL ON TABLE "public"."tournament_registrations" TO "anon";
GRANT ALL ON TABLE "public"."tournament_registrations" TO "authenticated";
GRANT ALL ON TABLE "public"."tournament_registrations" TO "service_role";



GRANT ALL ON TABLE "public"."tournaments" TO "anon";
GRANT ALL ON TABLE "public"."tournaments" TO "authenticated";
GRANT ALL ON TABLE "public"."tournaments" TO "service_role";



GRANT ALL ON TABLE "public"."user_achievements" TO "anon";
GRANT ALL ON TABLE "public"."user_achievements" TO "authenticated";
GRANT ALL ON TABLE "public"."user_achievements" TO "service_role";



GRANT ALL ON TABLE "public"."user_inventory" TO "anon";
GRANT ALL ON TABLE "public"."user_inventory" TO "authenticated";
GRANT ALL ON TABLE "public"."user_inventory" TO "service_role";









ALTER DEFAULT PRIVILEGES FOR ROLE "postgres" IN SCHEMA "public" GRANT ALL ON SEQUENCES TO "postgres";
ALTER DEFAULT PRIVILEGES FOR ROLE "postgres" IN SCHEMA "public" GRANT ALL ON SEQUENCES TO "anon";
ALTER DEFAULT PRIVILEGES FOR ROLE "postgres" IN SCHEMA "public" GRANT ALL ON SEQUENCES TO "authenticated";
ALTER DEFAULT PRIVILEGES FOR ROLE "postgres" IN SCHEMA "public" GRANT ALL ON SEQUENCES TO "service_role";






ALTER DEFAULT PRIVILEGES FOR ROLE "postgres" IN SCHEMA "public" GRANT ALL ON FUNCTIONS TO "postgres";
ALTER DEFAULT PRIVILEGES FOR ROLE "postgres" IN SCHEMA "public" GRANT ALL ON FUNCTIONS TO "anon";
ALTER DEFAULT PRIVILEGES FOR ROLE "postgres" IN SCHEMA "public" GRANT ALL ON FUNCTIONS TO "authenticated";
ALTER DEFAULT PRIVILEGES FOR ROLE "postgres" IN SCHEMA "public" GRANT ALL ON FUNCTIONS TO "service_role";






ALTER DEFAULT PRIVILEGES FOR ROLE "postgres" IN SCHEMA "public" GRANT ALL ON TABLES TO "postgres";
ALTER DEFAULT PRIVILEGES FOR ROLE "postgres" IN SCHEMA "public" GRANT ALL ON TABLES TO "anon";
ALTER DEFAULT PRIVILEGES FOR ROLE "postgres" IN SCHEMA "public" GRANT ALL ON TABLES TO "authenticated";
ALTER DEFAULT PRIVILEGES FOR ROLE "postgres" IN SCHEMA "public" GRANT ALL ON TABLES TO "service_role";































drop extension if exists "pg_net";

drop policy "Active championships viewable by everyone" on "public"."championships";

drop policy "championships_select_active" on "public"."championships";

drop policy "fantasy_gameweeks_select_active" on "public"."fantasy_gameweeks";

drop policy "fantasy_seasons_select_active" on "public"."fantasy_seasons";

drop policy "Participants can create matches" on "public"."matches";

drop policy "matches_delete_participants_unvalidated" on "public"."matches";

drop policy "matches_insert_participants" on "public"."matches";

drop policy "matches_update_participants_unvalidated" on "public"."matches";

drop policy "Active tournaments viewable by everyone" on "public"."tournaments";

drop policy "tournaments_select_active" on "public"."tournaments";


  create policy "Active championships viewable by everyone"
  on "public"."championships"
  as permissive
  for select
  to public
using (((status)::text = ANY ((ARRAY['active'::character varying, 'paused'::character varying, 'completed'::character varying])::text[])));



  create policy "championships_select_active"
  on "public"."championships"
  as permissive
  for select
  to public
using (((status)::text = ANY ((ARRAY['active'::character varying, 'paused'::character varying, 'completed'::character varying])::text[])));



  create policy "fantasy_gameweeks_select_active"
  on "public"."fantasy_gameweeks"
  as permissive
  for select
  to public
using ((EXISTS ( SELECT 1
   FROM public.fantasy_seasons
  WHERE ((fantasy_seasons.id = fantasy_gameweeks.season_id) AND ((fantasy_seasons.status)::text = ANY ((ARRAY['upcoming'::character varying, 'active'::character varying, 'completed'::character varying])::text[]))))));



  create policy "fantasy_seasons_select_active"
  on "public"."fantasy_seasons"
  as permissive
  for select
  to public
using (((status)::text = ANY ((ARRAY['upcoming'::character varying, 'active'::character varying, 'completed'::character varying])::text[])));



  create policy "Participants can create matches"
  on "public"."matches"
  as permissive
  for insert
  to public
with check (((((auth.uid() = team_a_player1_id) OR (auth.uid() = team_a_player2_id)) OR (auth.uid() = team_b_player1_id)) OR (auth.uid() = team_b_player2_id)));



  create policy "matches_delete_participants_unvalidated"
  on "public"."matches"
  as permissive
  for delete
  to public
using ((((((auth.uid() = team_a_player1_id) OR (auth.uid() = team_a_player2_id)) OR (auth.uid() = team_b_player1_id)) OR (auth.uid() = team_b_player2_id)) AND (is_validated = false)));



  create policy "matches_insert_participants"
  on "public"."matches"
  as permissive
  for insert
  to public
with check ((((((auth.uid() = team_a_player1_id) OR (auth.uid() = team_a_player2_id)) OR (auth.uid() = team_b_player1_id)) OR (auth.uid() = team_b_player2_id)) AND (team_a_player1_id <> team_a_player2_id) AND (team_b_player1_id <> team_b_player2_id) AND (team_a_player1_id <> team_b_player1_id) AND (team_a_player1_id <> team_b_player2_id) AND (team_a_player2_id <> team_b_player1_id) AND (team_a_player2_id <> team_b_player2_id)));



  create policy "matches_update_participants_unvalidated"
  on "public"."matches"
  as permissive
  for update
  to public
using ((((((auth.uid() = team_a_player1_id) OR (auth.uid() = team_a_player2_id)) OR (auth.uid() = team_b_player1_id)) OR (auth.uid() = team_b_player2_id)) AND (is_validated = false)));



  create policy "Active tournaments viewable by everyone"
  on "public"."tournaments"
  as permissive
  for select
  to public
using (((status)::text = ANY ((ARRAY['upcoming'::character varying, 'in_progress'::character varying, 'completed'::character varying])::text[])));



  create policy "tournaments_select_active"
  on "public"."tournaments"
  as permissive
  for select
  to public
using (((status)::text = ANY ((ARRAY['upcoming'::character varying, 'in_progress'::character varying, 'completed'::character varying])::text[])));



  create policy "Enable insert for users based on user_id"
  on "storage"."objects"
  as permissive
  for insert
  to public
with check (((bucket_id = 'avatars'::text) AND ((auth.uid())::text = (storage.foldername(name))[1])));



  create policy "Enable read access for all users"
  on "storage"."objects"
  as permissive
  for select
  to public
using ((bucket_id = 'avatars'::text));



  create policy "Users can update their own avatar"
  on "storage"."objects"
  as permissive
  for update
  to public
using (((bucket_id = 'avatars'::text) AND ((auth.uid())::text = (storage.foldername(name))[1])));



