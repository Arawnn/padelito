--
-- PostgreSQL database dump
--

-- Dumped from database version 17.9
-- Dumped by pg_dump version 17.9 (Debian 17.9-1.pgdg12+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: auth; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA auth;


--
-- Name: extensions; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA extensions;


--
-- Name: email(); Type: FUNCTION; Schema: auth; Owner: -
--

CREATE FUNCTION auth.email() RETURNS text
    LANGUAGE sql STABLE
    AS $$
    SELECT NULLIF(current_setting('request.jwt.claim.email', true), '');
$$;


--
-- Name: jwt(); Type: FUNCTION; Schema: auth; Owner: -
--

CREATE FUNCTION auth.jwt() RETURNS jsonb
    LANGUAGE sql STABLE
    AS $$
    SELECT COALESCE(NULLIF(current_setting('request.jwt.claims', true), '')::jsonb, '{}'::jsonb);
$$;


--
-- Name: role(); Type: FUNCTION; Schema: auth; Owner: -
--

CREATE FUNCTION auth.role() RETURNS text
    LANGUAGE sql STABLE
    AS $$
    SELECT COALESCE(NULLIF(current_setting('request.jwt.claim.role', true), ''), current_user);
$$;


--
-- Name: uid(); Type: FUNCTION; Schema: auth; Owner: -
--

CREATE FUNCTION auth.uid() RETURNS uuid
    LANGUAGE sql STABLE
    AS $$
    SELECT NULLIF(current_setting('request.jwt.claim.sub', true), '')::uuid;
$$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: achievements; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.achievements (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    code character varying(50) NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    icon_url text,
    rarity character varying(20) DEFAULT 'common'::character varying,
    criteria jsonb,
    reward_coins integer DEFAULT 0
);


--
-- Name: activity_feed; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activity_feed (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid,
    activity_type character varying(50) NOT NULL,
    content jsonb NOT NULL,
    visibility character varying(20) DEFAULT 'friends'::character varying,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: activity_reactions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activity_reactions (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    activity_id uuid,
    user_id uuid,
    reaction_type character varying(20) NOT NULL,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: bets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.bets (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    match_id uuid,
    bettor_id uuid,
    predicted_winner character varying(10) NOT NULL,
    bet_amount integer NOT NULL,
    odds numeric(5,2) NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying,
    potential_win integer,
    actual_win integer,
    placed_at timestamp with time zone DEFAULT now(),
    resolved_at timestamp with time zone,
    CONSTRAINT bets_bet_amount_check CHECK ((bet_amount > 0))
);


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: championship_rounds; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.championship_rounds (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    championship_id uuid,
    round_number integer NOT NULL,
    round_date date,
    status character varying(20) DEFAULT 'upcoming'::character varying
);


--
-- Name: championship_standings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.championship_standings (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    championship_id uuid,
    registration_id uuid,
    matches_played integer DEFAULT 0,
    matches_won integer DEFAULT 0,
    matches_lost integer DEFAULT 0,
    sets_won integer DEFAULT 0,
    sets_lost integer DEFAULT 0,
    games_won integer DEFAULT 0,
    games_lost integer DEFAULT 0,
    points integer DEFAULT 0
);


--
-- Name: championships; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.championships (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    name character varying(200) NOT NULL,
    description text,
    status character varying(20) DEFAULT 'active'::character varying,
    group_id uuid,
    start_date date,
    end_date date,
    created_by uuid,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: coin_transactions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.coin_transactions (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid,
    amount integer NOT NULL,
    transaction_type character varying(50) NOT NULL,
    reference_id uuid,
    description text,
    balance_after integer NOT NULL,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: comments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.comments (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    activity_id uuid,
    user_id uuid,
    content text NOT NULL,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: elo_history; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.elo_history (
    id uuid NOT NULL,
    player_id uuid NOT NULL,
    match_id uuid NOT NULL,
    elo_before integer NOT NULL,
    elo_after integer NOT NULL,
    elo_change integer NOT NULL,
    recorded_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    team character varying(1) NOT NULL,
    won boolean NOT NULL
);


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: fantasy_achievements; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_achievements (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    code character varying(50) NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    icon_url text,
    rarity character varying(20) DEFAULT 'common'::character varying,
    criteria jsonb
);


--
-- Name: fantasy_chips; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_chips (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    code character varying(50) NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    effect_type character varying(50) NOT NULL,
    can_use_multiple_times boolean DEFAULT false,
    max_uses_per_season integer DEFAULT 1
);


--
-- Name: fantasy_gameweek_rankings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_gameweek_rankings (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    gameweek_id uuid,
    team_id uuid,
    points integer DEFAULT 0,
    rank integer,
    rank_change integer DEFAULT 0,
    points_change integer DEFAULT 0,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: fantasy_gameweeks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_gameweeks (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    season_id uuid,
    gameweek_number integer NOT NULL,
    name character varying(100),
    start_date timestamp with time zone NOT NULL,
    end_date timestamp with time zone NOT NULL,
    deadline timestamp with time zone NOT NULL,
    is_active boolean DEFAULT false,
    is_completed boolean DEFAULT false,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: fantasy_league_members; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_league_members (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    league_id uuid,
    team_id uuid,
    joined_at timestamp with time zone DEFAULT now(),
    is_admin boolean DEFAULT false
);


--
-- Name: fantasy_leagues; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_leagues (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    season_id uuid,
    name character varying(200) NOT NULL,
    description text,
    league_code character varying(20) NOT NULL,
    type character varying(20) DEFAULT 'private'::character varying,
    max_teams integer DEFAULT 20,
    prize_pool integer DEFAULT 0,
    prizes jsonb,
    created_by uuid,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: fantasy_news; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_news (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    season_id uuid,
    title character varying(200) NOT NULL,
    content text NOT NULL,
    news_type character varying(50),
    related_players uuid[],
    is_featured boolean DEFAULT false,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: fantasy_player_performances; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_player_performances (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    player_id uuid,
    gameweek_id uuid,
    match_id uuid,
    played boolean DEFAULT true,
    victory boolean DEFAULT false,
    games_won integer DEFAULT 0,
    games_lost integer DEFAULT 0,
    clean_sheet boolean DEFAULT false,
    is_mvp boolean DEFAULT false,
    is_in_hat_trick boolean DEFAULT false,
    base_points integer DEFAULT 0,
    bonus_points integer DEFAULT 0,
    total_points integer DEFAULT 0,
    notes text,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: fantasy_player_price_history; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_player_price_history (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    player_id uuid,
    gameweek_id uuid,
    old_price integer NOT NULL,
    new_price integer NOT NULL,
    reason text,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: fantasy_players; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_players (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    season_id uuid,
    user_id uuid,
    player_name character varying(100) NOT NULL,
    "position" character varying(20) DEFAULT 'player'::character varying,
    club_name character varying(100),
    initial_price integer NOT NULL,
    current_price integer NOT NULL,
    elo_rating integer,
    total_points integer DEFAULT 0,
    matches_played integer DEFAULT 0,
    wins integer DEFAULT 0,
    losses integer DEFAULT 0,
    recent_form jsonb,
    is_available boolean DEFAULT true,
    is_injured boolean DEFAULT false,
    injury_return_date date,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);


--
-- Name: fantasy_seasons; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_seasons (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    status character varying(20) DEFAULT 'upcoming'::character varying,
    start_date date NOT NULL,
    end_date date NOT NULL,
    registration_deadline date,
    budget integer DEFAULT 500,
    team_size integer DEFAULT 8,
    max_same_club integer DEFAULT 3,
    points_victory integer DEFAULT 3,
    points_defeat integer DEFAULT 0,
    points_goals_for integer DEFAULT 1,
    points_goals_against integer DEFAULT '-1'::integer,
    points_clean_sheet integer DEFAULT 5,
    points_hat_trick integer DEFAULT 10,
    points_mvp integer DEFAULT 15,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);


--
-- Name: fantasy_team_achievements; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_team_achievements (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    team_id uuid,
    achievement_id uuid,
    unlocked_at timestamp with time zone DEFAULT now()
);


--
-- Name: fantasy_team_chips; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_team_chips (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    team_id uuid,
    chip_id uuid,
    gameweek_id uuid,
    used_at timestamp with time zone DEFAULT now()
);


--
-- Name: fantasy_team_rosters; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_team_rosters (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    team_id uuid,
    player_id uuid,
    is_captain boolean DEFAULT false,
    is_vice_captain boolean DEFAULT false,
    is_benched boolean DEFAULT false,
    position_order integer,
    acquired_at timestamp with time zone DEFAULT now(),
    acquisition_price integer NOT NULL
);


--
-- Name: fantasy_teams; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_teams (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    season_id uuid,
    user_id uuid,
    team_name character varying(100) NOT NULL,
    team_logo text,
    initial_budget integer DEFAULT 500,
    remaining_budget integer DEFAULT 500,
    team_value integer DEFAULT 0,
    total_points integer DEFAULT 0,
    global_rank integer,
    formation character varying(20) DEFAULT 'balanced'::character varying,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now()
);


--
-- Name: fantasy_transfers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fantasy_transfers (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    team_id uuid,
    gameweek_id uuid,
    player_out_id uuid,
    player_in_id uuid,
    transfer_cost integer DEFAULT 0,
    transfer_type character varying(20) DEFAULT 'standard'::character varying,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: friendships; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.friendships (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid,
    friend_id uuid,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: group_members; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.group_members (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    group_id uuid,
    user_id uuid,
    role character varying(20) DEFAULT 'member'::character varying,
    joined_at timestamp with time zone DEFAULT now()
);


--
-- Name: groups; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.groups (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    avatar_url text,
    created_by uuid,
    is_private boolean DEFAULT false,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: match_confirmations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.match_confirmations (
    id uuid NOT NULL,
    match_id uuid NOT NULL,
    player_id uuid NOT NULL,
    confirmed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: match_invitations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.match_invitations (
    id uuid NOT NULL,
    match_id uuid NOT NULL,
    invitee_id uuid NOT NULL,
    type character varying(10) NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    invited_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    responded_at timestamp(0) without time zone
);


--
-- Name: matches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.matches (
    id uuid NOT NULL,
    match_date timestamp(0) without time zone,
    court_name character varying(100),
    match_type character varying(20) DEFAULT 'friendly'::character varying NOT NULL,
    match_format character varying(20) DEFAULT 'doubles'::character varying NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    team_a_player1_id uuid NOT NULL,
    team_a_player2_id uuid,
    team_b_player1_id uuid,
    team_b_player2_id uuid,
    sets_detail json,
    sets_to_win smallint DEFAULT '2'::smallint NOT NULL,
    team_a_score integer,
    team_b_score integer,
    notes text,
    created_by uuid NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notifications (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid,
    notification_type character varying(50) NOT NULL,
    title character varying(200) NOT NULL,
    body text,
    data jsonb,
    is_read boolean DEFAULT false,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id uuid NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: profiles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.profiles (
    id uuid NOT NULL,
    username character varying(255) NOT NULL,
    level character varying(255) NOT NULL,
    display_name character varying(255),
    bio text,
    avatar_url character varying(255),
    dominant_hand character varying(255),
    preferred_position character varying(255),
    location character varying(255),
    elo_rating integer DEFAULT 0 NOT NULL,
    total_wins integer DEFAULT 0 NOT NULL,
    total_losses integer DEFAULT 0 NOT NULL,
    current_streak integer DEFAULT 0 NOT NULL,
    best_streak integer DEFAULT 0 NOT NULL,
    padel_coins integer DEFAULT 0 NOT NULL,
    is_public boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id uuid,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: shop_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.shop_items (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    item_type character varying(50) NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    image_url text,
    price integer NOT NULL,
    rarity character varying(20) DEFAULT 'common'::character varying,
    is_available boolean DEFAULT true,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: tournament_brackets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tournament_brackets (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    tournament_id uuid,
    round character varying(50),
    match_number integer,
    team_a_registration_id uuid,
    team_b_registration_id uuid,
    match_id uuid,
    winner_registration_id uuid,
    next_bracket_id uuid
);


--
-- Name: tournament_registrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tournament_registrations (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    tournament_id uuid,
    player1_id uuid,
    player2_id uuid,
    team_name character varying(100),
    registration_date timestamp with time zone DEFAULT now(),
    seed integer
);


--
-- Name: tournaments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tournaments (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    name character varying(200) NOT NULL,
    description text,
    tournament_type character varying(20) DEFAULT 'knockout'::character varying,
    status character varying(20) DEFAULT 'upcoming'::character varying,
    max_teams integer DEFAULT 8,
    start_date timestamp with time zone,
    end_date timestamp with time zone,
    group_id uuid,
    created_by uuid,
    settings jsonb,
    created_at timestamp with time zone DEFAULT now()
);


--
-- Name: user_achievements; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_achievements (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid,
    achievement_id uuid,
    unlocked_at timestamp with time zone DEFAULT now()
);


--
-- Name: user_inventory; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_inventory (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid,
    item_id uuid,
    purchased_at timestamp with time zone DEFAULT now(),
    is_equipped boolean DEFAULT false
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    two_factor_secret text,
    two_factor_recovery_codes text,
    two_factor_confirmed_at timestamp(0) without time zone
);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: achievements achievements_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.achievements
    ADD CONSTRAINT achievements_code_key UNIQUE (code);


--
-- Name: achievements achievements_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.achievements
    ADD CONSTRAINT achievements_pkey PRIMARY KEY (id);


--
-- Name: activity_feed activity_feed_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_feed
    ADD CONSTRAINT activity_feed_pkey PRIMARY KEY (id);


--
-- Name: activity_reactions activity_reactions_activity_id_user_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_reactions
    ADD CONSTRAINT activity_reactions_activity_id_user_id_key UNIQUE (activity_id, user_id);


--
-- Name: activity_reactions activity_reactions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_reactions
    ADD CONSTRAINT activity_reactions_pkey PRIMARY KEY (id);


--
-- Name: bets bets_match_id_bettor_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bets
    ADD CONSTRAINT bets_match_id_bettor_id_key UNIQUE (match_id, bettor_id);


--
-- Name: bets bets_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bets
    ADD CONSTRAINT bets_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: championship_rounds championship_rounds_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.championship_rounds
    ADD CONSTRAINT championship_rounds_pkey PRIMARY KEY (id);


--
-- Name: championship_standings championship_standings_championship_id_registration_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.championship_standings
    ADD CONSTRAINT championship_standings_championship_id_registration_id_key UNIQUE (championship_id, registration_id);


--
-- Name: championship_standings championship_standings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.championship_standings
    ADD CONSTRAINT championship_standings_pkey PRIMARY KEY (id);


--
-- Name: championships championships_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.championships
    ADD CONSTRAINT championships_pkey PRIMARY KEY (id);


--
-- Name: coin_transactions coin_transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.coin_transactions
    ADD CONSTRAINT coin_transactions_pkey PRIMARY KEY (id);


--
-- Name: comments comments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_pkey PRIMARY KEY (id);


--
-- Name: elo_history elo_history_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.elo_history
    ADD CONSTRAINT elo_history_pkey PRIMARY KEY (id);


--
-- Name: elo_history elo_history_player_id_match_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.elo_history
    ADD CONSTRAINT elo_history_player_id_match_id_unique UNIQUE (player_id, match_id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: fantasy_achievements fantasy_achievements_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_achievements
    ADD CONSTRAINT fantasy_achievements_code_key UNIQUE (code);


--
-- Name: fantasy_achievements fantasy_achievements_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_achievements
    ADD CONSTRAINT fantasy_achievements_pkey PRIMARY KEY (id);


--
-- Name: fantasy_chips fantasy_chips_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_chips
    ADD CONSTRAINT fantasy_chips_code_key UNIQUE (code);


--
-- Name: fantasy_chips fantasy_chips_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_chips
    ADD CONSTRAINT fantasy_chips_pkey PRIMARY KEY (id);


--
-- Name: fantasy_gameweek_rankings fantasy_gameweek_rankings_gameweek_id_team_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_gameweek_rankings
    ADD CONSTRAINT fantasy_gameweek_rankings_gameweek_id_team_id_key UNIQUE (gameweek_id, team_id);


--
-- Name: fantasy_gameweek_rankings fantasy_gameweek_rankings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_gameweek_rankings
    ADD CONSTRAINT fantasy_gameweek_rankings_pkey PRIMARY KEY (id);


--
-- Name: fantasy_gameweeks fantasy_gameweeks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_gameweeks
    ADD CONSTRAINT fantasy_gameweeks_pkey PRIMARY KEY (id);


--
-- Name: fantasy_gameweeks fantasy_gameweeks_season_id_gameweek_number_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_gameweeks
    ADD CONSTRAINT fantasy_gameweeks_season_id_gameweek_number_key UNIQUE (season_id, gameweek_number);


--
-- Name: fantasy_league_members fantasy_league_members_league_id_team_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_league_members
    ADD CONSTRAINT fantasy_league_members_league_id_team_id_key UNIQUE (league_id, team_id);


--
-- Name: fantasy_league_members fantasy_league_members_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_league_members
    ADD CONSTRAINT fantasy_league_members_pkey PRIMARY KEY (id);


--
-- Name: fantasy_leagues fantasy_leagues_league_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_leagues
    ADD CONSTRAINT fantasy_leagues_league_code_key UNIQUE (league_code);


--
-- Name: fantasy_leagues fantasy_leagues_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_leagues
    ADD CONSTRAINT fantasy_leagues_pkey PRIMARY KEY (id);


--
-- Name: fantasy_news fantasy_news_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_news
    ADD CONSTRAINT fantasy_news_pkey PRIMARY KEY (id);


--
-- Name: fantasy_player_performances fantasy_player_performances_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_player_performances
    ADD CONSTRAINT fantasy_player_performances_pkey PRIMARY KEY (id);


--
-- Name: fantasy_player_performances fantasy_player_performances_player_id_gameweek_id_match_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_player_performances
    ADD CONSTRAINT fantasy_player_performances_player_id_gameweek_id_match_id_key UNIQUE (player_id, gameweek_id, match_id);


--
-- Name: fantasy_player_price_history fantasy_player_price_history_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_player_price_history
    ADD CONSTRAINT fantasy_player_price_history_pkey PRIMARY KEY (id);


--
-- Name: fantasy_players fantasy_players_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_players
    ADD CONSTRAINT fantasy_players_pkey PRIMARY KEY (id);


--
-- Name: fantasy_players fantasy_players_season_id_user_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_players
    ADD CONSTRAINT fantasy_players_season_id_user_id_key UNIQUE (season_id, user_id);


--
-- Name: fantasy_seasons fantasy_seasons_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_seasons
    ADD CONSTRAINT fantasy_seasons_pkey PRIMARY KEY (id);


--
-- Name: fantasy_team_achievements fantasy_team_achievements_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_team_achievements
    ADD CONSTRAINT fantasy_team_achievements_pkey PRIMARY KEY (id);


--
-- Name: fantasy_team_achievements fantasy_team_achievements_team_id_achievement_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_team_achievements
    ADD CONSTRAINT fantasy_team_achievements_team_id_achievement_id_key UNIQUE (team_id, achievement_id);


--
-- Name: fantasy_team_chips fantasy_team_chips_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_team_chips
    ADD CONSTRAINT fantasy_team_chips_pkey PRIMARY KEY (id);


--
-- Name: fantasy_team_chips fantasy_team_chips_team_id_chip_id_gameweek_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_team_chips
    ADD CONSTRAINT fantasy_team_chips_team_id_chip_id_gameweek_id_key UNIQUE (team_id, chip_id, gameweek_id);


--
-- Name: fantasy_team_rosters fantasy_team_rosters_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_team_rosters
    ADD CONSTRAINT fantasy_team_rosters_pkey PRIMARY KEY (id);


--
-- Name: fantasy_team_rosters fantasy_team_rosters_team_id_player_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_team_rosters
    ADD CONSTRAINT fantasy_team_rosters_team_id_player_id_key UNIQUE (team_id, player_id);


--
-- Name: fantasy_teams fantasy_teams_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_teams
    ADD CONSTRAINT fantasy_teams_pkey PRIMARY KEY (id);


--
-- Name: fantasy_teams fantasy_teams_season_id_user_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_teams
    ADD CONSTRAINT fantasy_teams_season_id_user_id_key UNIQUE (season_id, user_id);


--
-- Name: fantasy_transfers fantasy_transfers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_transfers
    ADD CONSTRAINT fantasy_transfers_pkey PRIMARY KEY (id);


--
-- Name: friendships friendships_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.friendships
    ADD CONSTRAINT friendships_pkey PRIMARY KEY (id);


--
-- Name: friendships friendships_user_id_friend_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.friendships
    ADD CONSTRAINT friendships_user_id_friend_id_key UNIQUE (user_id, friend_id);


--
-- Name: group_members group_members_group_id_user_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.group_members
    ADD CONSTRAINT group_members_group_id_user_id_key UNIQUE (group_id, user_id);


--
-- Name: group_members group_members_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.group_members
    ADD CONSTRAINT group_members_pkey PRIMARY KEY (id);


--
-- Name: groups groups_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.groups
    ADD CONSTRAINT groups_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: match_confirmations match_confirmations_match_id_player_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match_confirmations
    ADD CONSTRAINT match_confirmations_match_id_player_id_unique UNIQUE (match_id, player_id);


--
-- Name: match_confirmations match_confirmations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match_confirmations
    ADD CONSTRAINT match_confirmations_pkey PRIMARY KEY (id);


--
-- Name: match_invitations match_invitations_match_id_invitee_id_type_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match_invitations
    ADD CONSTRAINT match_invitations_match_id_invitee_id_type_unique UNIQUE (match_id, invitee_id, type);


--
-- Name: match_invitations match_invitations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match_invitations
    ADD CONSTRAINT match_invitations_pkey PRIMARY KEY (id);


--
-- Name: matches matches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.matches
    ADD CONSTRAINT matches_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: profiles profiles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.profiles
    ADD CONSTRAINT profiles_pkey PRIMARY KEY (id);


--
-- Name: profiles profiles_username_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.profiles
    ADD CONSTRAINT profiles_username_unique UNIQUE (username);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: shop_items shop_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.shop_items
    ADD CONSTRAINT shop_items_pkey PRIMARY KEY (id);


--
-- Name: tournament_brackets tournament_brackets_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament_brackets
    ADD CONSTRAINT tournament_brackets_pkey PRIMARY KEY (id);


--
-- Name: tournament_registrations tournament_registrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament_registrations
    ADD CONSTRAINT tournament_registrations_pkey PRIMARY KEY (id);


--
-- Name: tournament_registrations tournament_registrations_tournament_id_player1_id_player2_i_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament_registrations
    ADD CONSTRAINT tournament_registrations_tournament_id_player1_id_player2_i_key UNIQUE (tournament_id, player1_id, player2_id);


--
-- Name: tournaments tournaments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournaments
    ADD CONSTRAINT tournaments_pkey PRIMARY KEY (id);


--
-- Name: user_achievements user_achievements_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_achievements
    ADD CONSTRAINT user_achievements_pkey PRIMARY KEY (id);


--
-- Name: user_achievements user_achievements_user_id_achievement_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_achievements
    ADD CONSTRAINT user_achievements_user_id_achievement_id_key UNIQUE (user_id, achievement_id);


--
-- Name: user_inventory user_inventory_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_inventory
    ADD CONSTRAINT user_inventory_pkey PRIMARY KEY (id);


--
-- Name: user_inventory user_inventory_user_id_item_id_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_inventory
    ADD CONSTRAINT user_inventory_user_id_item_id_key UNIQUE (user_id, item_id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: elo_history_player_id_recorded_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX elo_history_player_id_recorded_at_index ON public.elo_history USING btree (player_id, recorded_at);


--
-- Name: idx_activity_feed_user; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_activity_feed_user ON public.activity_feed USING btree (user_id, created_at DESC);


--
-- Name: idx_bets_match; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_bets_match ON public.bets USING btree (match_id);


--
-- Name: idx_bets_user; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_bets_user ON public.bets USING btree (bettor_id);


--
-- Name: idx_fantasy_gameweek_rankings; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_fantasy_gameweek_rankings ON public.fantasy_gameweek_rankings USING btree (gameweek_id, rank);


--
-- Name: idx_fantasy_league_members; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_fantasy_league_members ON public.fantasy_league_members USING btree (league_id, team_id);


--
-- Name: idx_fantasy_performances_gameweek; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_fantasy_performances_gameweek ON public.fantasy_player_performances USING btree (gameweek_id);


--
-- Name: idx_fantasy_performances_player; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_fantasy_performances_player ON public.fantasy_player_performances USING btree (player_id);


--
-- Name: idx_fantasy_players_points; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_fantasy_players_points ON public.fantasy_players USING btree (total_points DESC);


--
-- Name: idx_fantasy_players_price; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_fantasy_players_price ON public.fantasy_players USING btree (current_price DESC);


--
-- Name: idx_fantasy_players_season; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_fantasy_players_season ON public.fantasy_players USING btree (season_id, is_available);


--
-- Name: idx_fantasy_teams_rank; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_fantasy_teams_rank ON public.fantasy_teams USING btree (global_rank);


--
-- Name: idx_fantasy_teams_season; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_fantasy_teams_season ON public.fantasy_teams USING btree (season_id);


--
-- Name: idx_notifications_user; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_notifications_user ON public.notifications USING btree (user_id, is_read, created_at DESC);


--
-- Name: idx_tournament_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_tournament_status ON public.tournaments USING btree (status, start_date);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: activity_feed activity_feed_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_feed
    ADD CONSTRAINT activity_feed_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: activity_reactions activity_reactions_activity_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_reactions
    ADD CONSTRAINT activity_reactions_activity_id_fkey FOREIGN KEY (activity_id) REFERENCES public.activity_feed(id) ON DELETE CASCADE;


--
-- Name: activity_reactions activity_reactions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_reactions
    ADD CONSTRAINT activity_reactions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: bets bets_bettor_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bets
    ADD CONSTRAINT bets_bettor_id_fkey FOREIGN KEY (bettor_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: bets bets_match_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.bets
    ADD CONSTRAINT bets_match_id_fkey FOREIGN KEY (match_id) REFERENCES public.matches(id) ON DELETE CASCADE;


--
-- Name: championship_rounds championship_rounds_championship_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.championship_rounds
    ADD CONSTRAINT championship_rounds_championship_id_fkey FOREIGN KEY (championship_id) REFERENCES public.championships(id) ON DELETE CASCADE;


--
-- Name: championship_standings championship_standings_championship_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.championship_standings
    ADD CONSTRAINT championship_standings_championship_id_fkey FOREIGN KEY (championship_id) REFERENCES public.championships(id) ON DELETE CASCADE;


--
-- Name: championship_standings championship_standings_registration_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.championship_standings
    ADD CONSTRAINT championship_standings_registration_id_fkey FOREIGN KEY (registration_id) REFERENCES public.tournament_registrations(id);


--
-- Name: championships championships_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.championships
    ADD CONSTRAINT championships_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.profiles(id);


--
-- Name: championships championships_group_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.championships
    ADD CONSTRAINT championships_group_id_fkey FOREIGN KEY (group_id) REFERENCES public.groups(id);


--
-- Name: coin_transactions coin_transactions_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.coin_transactions
    ADD CONSTRAINT coin_transactions_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: comments comments_activity_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_activity_id_fkey FOREIGN KEY (activity_id) REFERENCES public.activity_feed(id) ON DELETE CASCADE;


--
-- Name: comments comments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: elo_history elo_history_match_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.elo_history
    ADD CONSTRAINT elo_history_match_id_foreign FOREIGN KEY (match_id) REFERENCES public.matches(id) ON DELETE CASCADE;


--
-- Name: elo_history elo_history_player_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.elo_history
    ADD CONSTRAINT elo_history_player_id_foreign FOREIGN KEY (player_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: fantasy_gameweek_rankings fantasy_gameweek_rankings_gameweek_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_gameweek_rankings
    ADD CONSTRAINT fantasy_gameweek_rankings_gameweek_id_fkey FOREIGN KEY (gameweek_id) REFERENCES public.fantasy_gameweeks(id) ON DELETE CASCADE;


--
-- Name: fantasy_gameweek_rankings fantasy_gameweek_rankings_team_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_gameweek_rankings
    ADD CONSTRAINT fantasy_gameweek_rankings_team_id_fkey FOREIGN KEY (team_id) REFERENCES public.fantasy_teams(id) ON DELETE CASCADE;


--
-- Name: fantasy_gameweeks fantasy_gameweeks_season_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_gameweeks
    ADD CONSTRAINT fantasy_gameweeks_season_id_fkey FOREIGN KEY (season_id) REFERENCES public.fantasy_seasons(id) ON DELETE CASCADE;


--
-- Name: fantasy_league_members fantasy_league_members_league_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_league_members
    ADD CONSTRAINT fantasy_league_members_league_id_fkey FOREIGN KEY (league_id) REFERENCES public.fantasy_leagues(id) ON DELETE CASCADE;


--
-- Name: fantasy_league_members fantasy_league_members_team_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_league_members
    ADD CONSTRAINT fantasy_league_members_team_id_fkey FOREIGN KEY (team_id) REFERENCES public.fantasy_teams(id) ON DELETE CASCADE;


--
-- Name: fantasy_leagues fantasy_leagues_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_leagues
    ADD CONSTRAINT fantasy_leagues_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.profiles(id);


--
-- Name: fantasy_leagues fantasy_leagues_season_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_leagues
    ADD CONSTRAINT fantasy_leagues_season_id_fkey FOREIGN KEY (season_id) REFERENCES public.fantasy_seasons(id) ON DELETE CASCADE;


--
-- Name: fantasy_news fantasy_news_season_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_news
    ADD CONSTRAINT fantasy_news_season_id_fkey FOREIGN KEY (season_id) REFERENCES public.fantasy_seasons(id) ON DELETE CASCADE;


--
-- Name: fantasy_player_performances fantasy_player_performances_gameweek_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_player_performances
    ADD CONSTRAINT fantasy_player_performances_gameweek_id_fkey FOREIGN KEY (gameweek_id) REFERENCES public.fantasy_gameweeks(id) ON DELETE CASCADE;


--
-- Name: fantasy_player_performances fantasy_player_performances_match_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_player_performances
    ADD CONSTRAINT fantasy_player_performances_match_id_fkey FOREIGN KEY (match_id) REFERENCES public.matches(id) ON DELETE CASCADE;


--
-- Name: fantasy_player_performances fantasy_player_performances_player_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_player_performances
    ADD CONSTRAINT fantasy_player_performances_player_id_fkey FOREIGN KEY (player_id) REFERENCES public.fantasy_players(id) ON DELETE CASCADE;


--
-- Name: fantasy_player_price_history fantasy_player_price_history_gameweek_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_player_price_history
    ADD CONSTRAINT fantasy_player_price_history_gameweek_id_fkey FOREIGN KEY (gameweek_id) REFERENCES public.fantasy_gameweeks(id) ON DELETE CASCADE;


--
-- Name: fantasy_player_price_history fantasy_player_price_history_player_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_player_price_history
    ADD CONSTRAINT fantasy_player_price_history_player_id_fkey FOREIGN KEY (player_id) REFERENCES public.fantasy_players(id) ON DELETE CASCADE;


--
-- Name: fantasy_players fantasy_players_season_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_players
    ADD CONSTRAINT fantasy_players_season_id_fkey FOREIGN KEY (season_id) REFERENCES public.fantasy_seasons(id) ON DELETE CASCADE;


--
-- Name: fantasy_players fantasy_players_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_players
    ADD CONSTRAINT fantasy_players_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: fantasy_team_achievements fantasy_team_achievements_achievement_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_team_achievements
    ADD CONSTRAINT fantasy_team_achievements_achievement_id_fkey FOREIGN KEY (achievement_id) REFERENCES public.fantasy_achievements(id);


--
-- Name: fantasy_team_achievements fantasy_team_achievements_team_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_team_achievements
    ADD CONSTRAINT fantasy_team_achievements_team_id_fkey FOREIGN KEY (team_id) REFERENCES public.fantasy_teams(id) ON DELETE CASCADE;


--
-- Name: fantasy_team_chips fantasy_team_chips_chip_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_team_chips
    ADD CONSTRAINT fantasy_team_chips_chip_id_fkey FOREIGN KEY (chip_id) REFERENCES public.fantasy_chips(id);


--
-- Name: fantasy_team_chips fantasy_team_chips_gameweek_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_team_chips
    ADD CONSTRAINT fantasy_team_chips_gameweek_id_fkey FOREIGN KEY (gameweek_id) REFERENCES public.fantasy_gameweeks(id);


--
-- Name: fantasy_team_chips fantasy_team_chips_team_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_team_chips
    ADD CONSTRAINT fantasy_team_chips_team_id_fkey FOREIGN KEY (team_id) REFERENCES public.fantasy_teams(id) ON DELETE CASCADE;


--
-- Name: fantasy_team_rosters fantasy_team_rosters_player_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_team_rosters
    ADD CONSTRAINT fantasy_team_rosters_player_id_fkey FOREIGN KEY (player_id) REFERENCES public.fantasy_players(id) ON DELETE CASCADE;


--
-- Name: fantasy_team_rosters fantasy_team_rosters_team_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_team_rosters
    ADD CONSTRAINT fantasy_team_rosters_team_id_fkey FOREIGN KEY (team_id) REFERENCES public.fantasy_teams(id) ON DELETE CASCADE;


--
-- Name: fantasy_teams fantasy_teams_season_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_teams
    ADD CONSTRAINT fantasy_teams_season_id_fkey FOREIGN KEY (season_id) REFERENCES public.fantasy_seasons(id) ON DELETE CASCADE;


--
-- Name: fantasy_teams fantasy_teams_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_teams
    ADD CONSTRAINT fantasy_teams_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: fantasy_transfers fantasy_transfers_gameweek_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_transfers
    ADD CONSTRAINT fantasy_transfers_gameweek_id_fkey FOREIGN KEY (gameweek_id) REFERENCES public.fantasy_gameweeks(id) ON DELETE CASCADE;


--
-- Name: fantasy_transfers fantasy_transfers_player_in_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_transfers
    ADD CONSTRAINT fantasy_transfers_player_in_id_fkey FOREIGN KEY (player_in_id) REFERENCES public.fantasy_players(id);


--
-- Name: fantasy_transfers fantasy_transfers_player_out_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_transfers
    ADD CONSTRAINT fantasy_transfers_player_out_id_fkey FOREIGN KEY (player_out_id) REFERENCES public.fantasy_players(id);


--
-- Name: fantasy_transfers fantasy_transfers_team_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fantasy_transfers
    ADD CONSTRAINT fantasy_transfers_team_id_fkey FOREIGN KEY (team_id) REFERENCES public.fantasy_teams(id) ON DELETE CASCADE;


--
-- Name: friendships friendships_friend_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.friendships
    ADD CONSTRAINT friendships_friend_id_fkey FOREIGN KEY (friend_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: friendships friendships_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.friendships
    ADD CONSTRAINT friendships_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: group_members group_members_group_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.group_members
    ADD CONSTRAINT group_members_group_id_fkey FOREIGN KEY (group_id) REFERENCES public.groups(id) ON DELETE CASCADE;


--
-- Name: group_members group_members_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.group_members
    ADD CONSTRAINT group_members_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: groups groups_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.groups
    ADD CONSTRAINT groups_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.profiles(id);


--
-- Name: match_confirmations match_confirmations_match_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match_confirmations
    ADD CONSTRAINT match_confirmations_match_id_foreign FOREIGN KEY (match_id) REFERENCES public.matches(id) ON DELETE CASCADE;


--
-- Name: match_confirmations match_confirmations_player_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match_confirmations
    ADD CONSTRAINT match_confirmations_player_id_foreign FOREIGN KEY (player_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: match_invitations match_invitations_invitee_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match_invitations
    ADD CONSTRAINT match_invitations_invitee_id_foreign FOREIGN KEY (invitee_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: match_invitations match_invitations_match_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.match_invitations
    ADD CONSTRAINT match_invitations_match_id_foreign FOREIGN KEY (match_id) REFERENCES public.matches(id) ON DELETE CASCADE;


--
-- Name: matches matches_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.matches
    ADD CONSTRAINT matches_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.profiles(id);


--
-- Name: matches matches_team_a_player1_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.matches
    ADD CONSTRAINT matches_team_a_player1_id_foreign FOREIGN KEY (team_a_player1_id) REFERENCES public.profiles(id);


--
-- Name: matches matches_team_a_player2_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.matches
    ADD CONSTRAINT matches_team_a_player2_id_foreign FOREIGN KEY (team_a_player2_id) REFERENCES public.profiles(id) ON DELETE SET NULL;


--
-- Name: matches matches_team_b_player1_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.matches
    ADD CONSTRAINT matches_team_b_player1_id_foreign FOREIGN KEY (team_b_player1_id) REFERENCES public.profiles(id) ON DELETE SET NULL;


--
-- Name: matches matches_team_b_player2_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.matches
    ADD CONSTRAINT matches_team_b_player2_id_foreign FOREIGN KEY (team_b_player2_id) REFERENCES public.profiles(id) ON DELETE SET NULL;


--
-- Name: notifications notifications_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: profiles profiles_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.profiles
    ADD CONSTRAINT profiles_id_foreign FOREIGN KEY (id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: tournament_brackets tournament_brackets_match_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament_brackets
    ADD CONSTRAINT tournament_brackets_match_id_fkey FOREIGN KEY (match_id) REFERENCES public.matches(id);


--
-- Name: tournament_brackets tournament_brackets_next_bracket_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament_brackets
    ADD CONSTRAINT tournament_brackets_next_bracket_id_fkey FOREIGN KEY (next_bracket_id) REFERENCES public.tournament_brackets(id);


--
-- Name: tournament_brackets tournament_brackets_team_a_registration_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament_brackets
    ADD CONSTRAINT tournament_brackets_team_a_registration_id_fkey FOREIGN KEY (team_a_registration_id) REFERENCES public.tournament_registrations(id);


--
-- Name: tournament_brackets tournament_brackets_team_b_registration_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament_brackets
    ADD CONSTRAINT tournament_brackets_team_b_registration_id_fkey FOREIGN KEY (team_b_registration_id) REFERENCES public.tournament_registrations(id);


--
-- Name: tournament_brackets tournament_brackets_tournament_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament_brackets
    ADD CONSTRAINT tournament_brackets_tournament_id_fkey FOREIGN KEY (tournament_id) REFERENCES public.tournaments(id) ON DELETE CASCADE;


--
-- Name: tournament_brackets tournament_brackets_winner_registration_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament_brackets
    ADD CONSTRAINT tournament_brackets_winner_registration_id_fkey FOREIGN KEY (winner_registration_id) REFERENCES public.tournament_registrations(id);


--
-- Name: tournament_registrations tournament_registrations_player1_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament_registrations
    ADD CONSTRAINT tournament_registrations_player1_id_fkey FOREIGN KEY (player1_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: tournament_registrations tournament_registrations_player2_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament_registrations
    ADD CONSTRAINT tournament_registrations_player2_id_fkey FOREIGN KEY (player2_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: tournament_registrations tournament_registrations_tournament_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournament_registrations
    ADD CONSTRAINT tournament_registrations_tournament_id_fkey FOREIGN KEY (tournament_id) REFERENCES public.tournaments(id) ON DELETE CASCADE;


--
-- Name: tournaments tournaments_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournaments
    ADD CONSTRAINT tournaments_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.profiles(id);


--
-- Name: tournaments tournaments_group_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tournaments
    ADD CONSTRAINT tournaments_group_id_fkey FOREIGN KEY (group_id) REFERENCES public.groups(id);


--
-- Name: user_achievements user_achievements_achievement_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_achievements
    ADD CONSTRAINT user_achievements_achievement_id_fkey FOREIGN KEY (achievement_id) REFERENCES public.achievements(id) ON DELETE CASCADE;


--
-- Name: user_achievements user_achievements_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_achievements
    ADD CONSTRAINT user_achievements_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- Name: user_inventory user_inventory_item_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_inventory
    ADD CONSTRAINT user_inventory_item_id_fkey FOREIGN KEY (item_id) REFERENCES public.shop_items(id) ON DELETE CASCADE;


--
-- Name: user_inventory user_inventory_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_inventory
    ADD CONSTRAINT user_inventory_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.profiles(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

--
-- PostgreSQL database dump
--

-- Dumped from database version 17.9
-- Dumped by pg_dump version 17.9 (Debian 17.9-1.pgdg12+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2025_08_14_170933_add_two_factor_columns_to_users_table	1
5	2026_02_20_214919_create_personal_access_tokens_table	1
6	2026_04_06_000000_create_profiles_table	1
7	2026_04_19_000001_create_matches_table	1
8	2026_04_19_000002_create_match_invitations_table	1
9	2026_04_19_000003_create_match_confirmations_table	1
10	2026_04_19_000004_create_elo_history_table	1
11	2026_04_28_000001_refine_elo_history_and_remove_match_elo_snapshot	1
12	2026_04_30_000000_create_legacy_supabase_public_schema	1
\.


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 12, true);


--
-- PostgreSQL database dump complete
--
