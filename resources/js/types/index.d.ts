import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface Driver {
    id: number;
    name: string;
    surname: string;
    nationality?: string;
    status: boolean;
    teams?: Team[];
    races?: number;
    wins?: number;
    second_positions?: number;
    third_positions?: number;
    points?: number;
}

export interface Participation {
    id: number;
    driver_id: number;
    team_id: number;
    race_id: number;
    position: number;
    status: string;
    points: number;
    uncertainty: number;
}

export interface Race {
    id: number;
    name: string;
    date: Date;
    winner?: Driver;
    second?: Driver;
    third?: Driver;
    better?: Driver;
}

export interface Team {
    id: number;
    name: string;
    nationality: string;
    status: boolean;
    drivers?: Driver[];
    races?: number;
    wins?: number;
    second_positions?: number;
    third_positions?: number;
    points?: number;
}
