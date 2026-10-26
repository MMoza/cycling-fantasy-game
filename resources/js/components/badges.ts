export interface BadgeItem {
    code: string;
    name: string;
    description: string;
    category: string;
    mode: string;
    svg: string;
    level?: number;
    levels?: number[];
    level_index?: number;
    progress: number;
    target: number;
    level_label?: string | null;
    special?: boolean;
    display?: string;
    overlay?: number | null;
    awarded_at?: string | null;
}
