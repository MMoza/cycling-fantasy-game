export interface User {
    id: string;
    name: string;
    email: string;
    email_verified_at?: string;
    avatar?: string | null;
    is_admin?: boolean;
}

export interface League {
    id: string;
    name: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
