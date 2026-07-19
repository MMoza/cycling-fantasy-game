export interface League {
    id: string;
    name: string;
    invite_code: string;
    owner_id: string;
    competition: {
        name: string;
        year: number;
        coverImageUrl?: string | null;
        logoImageUrl?: string | null;
    };
    scoring_system: {
        name: string;
        type: string;
        description: string;
        rules: {
            type: string;
            label: string;
            context: string;
            points: number;
            difficulty: number | null;
            position: number | null;
        }[];
    };
    is_public: boolean;
    is_official: boolean;
    max_players: number;
    member_count: number;
    is_owner: boolean;
    progress: {
        current_stage: number;
        total_stages: number;
    };
}

export interface Stage {
    id: string;
    number: number;
    name: string;
    date: string;
    type: string;
    distance: string | null;
    status: string;
}

export interface LeaderboardEntry {
    rank: number;
    user_id: string;
    user_name: string;
    avatar?: string | null;
    points: number;
    behind_leader: number;
    is_current_user: boolean;
    is_online?: boolean;
    previous_rank: number | null;
    rank_change: number | null;
    winner_streak?: number;
}

export interface ActivityLog {
    id: string;
    type: 'competition_start' | 'stage_start' | 'stage_end' | 'competition_end' | 'predictions_locked';
    title: string;
    description: string | null;
    data: Record<string, unknown> | null;
    created_at: string;
}

export interface NextStage {
    id: string;
    number: number;
    name: string;
    date: string;
    type: string;
    type_value: string;
    distance: string | null;
    distance_value: number | null;
    origin: string;
    destination: string;
    status: string;
    scheduled_start: string | null;
    difficulty: number | null;
    has_predictions: boolean;
}

export interface UserPosition {
    rank: string;
    points: string;
    behind_leader: string;
}
