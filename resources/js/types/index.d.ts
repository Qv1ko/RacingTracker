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

export interface DriverForm {
    id: number;
    name: string;
    surname: string;
    nationality: string;
}

export interface DriverProfile {
    id: number;
    name: string;
    surname: string;
    nationality: string;
}

export interface DriverData {
    id: number;
    name: string;
    surname: string;
    nationality: string;
    teams: string[];
    races: number;
    wins: number;
    second_position: number;
    third_position: number;
    points: number;
}

export interface TeamForm {
    id: number;
    name: string;
    nationality: string;
}

export interface TeamProfile {
    id: number;
    name: string;
    nationality: string;
}

export interface TeamData {
    id: number;
    name: string;
    nationality: string;
    drivers?: string[];
    races: number;
    wins: number;
    second_position: number;
    third_position: number;
    points: number;
}
