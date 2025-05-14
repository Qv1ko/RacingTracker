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
    // index
    teams?: Team[];
    races?: number;
    wins?: number;
    second_positions?: number;
    third_positions?: number;
    points?: number;
    // show
    seasons?: number;
    championshipsCount?: number;
    maxPoints?: number;
    activity?: { status: string; race_id: number; name: string; date: string }[];
    info?: {
        firstRace?: Race;
        lastRace?: Race;
        firstWin?: Race;
        lastWin?: Race;
        winPercentage: number;
        podiums: number;
        podiumPercentage: number;
        withoutPosition: number;
        raking: { driver_id: number; points: number; position: number };
        championships: string[];
    };
    pointsHistory?: { race: string; date: string; points: number }[];
    positionsHistory?: { position: string; times: number }[];
    teammates?: Driver[];
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
    // index
    winner?: { id: number; name: string; surname: string; nationality?: string; team?: Team };
    second?: { id: number; name: string; surname: string; nationality?: string; team?: Team };
    third?: { id: number; name: string; surname: string; nationality?: string; team?: Team };
    better?: { id: number; name: string; surname: string; nationality?: string; team?: Team };
    // show
    result?: {
        position: string;
        driver: Driver;
        points: number;
        pointsDiff: number;
        team: Team;
        teamPoints: number;
        teamPointsDiff: number;
    }[];
    driverStandings?: { position: string; driver: Driver; points: number; gap: number }[];
    teamStandings?: { position: string; team: Team; points: number; gap: number }[];
    more?: Race[];
}

export interface Season {
    season: string;
    // index
    driverResults?: { position: number; driver: Driver; pointsDiff: number; points: number; startingPoints: number }[];
    teamResults?: { position: number; team: Team; pointsDiff: number; points: number; startingPoints: number }[];
    racesCount?: number;
    drivers?: number;
    teams?: number;
    // show
    info: {
        firstRace?: Race;
        lastRace?: Race;
        mostWins?: { driver_id: number; driver: Driver; wins: number }[];
        mostPodiums?: { driver_id: number; driver: Driver; podiums: number }[];
    };
    driverStandings?: { position: string; driver: Driver; points: number; gap: number }[];
    driversPoints: { driver: Driver; pointsHistory: { race: string; date: string; points: number }[] }[];
    teamStandings?: { position: string; team: Team; points: number; gap: number }[];
    teamsPoints?: { team: Team; pointsHistory: { race: string; date: string; points: number }[] }[];
    races: Race[];
}

export interface Team {
    id: number;
    name: string;
    nationality: string;
    status: boolean;
    // index
    races?: number;
    wins?: number;
    second_positions?: number;
    third_positions?: number;
    points?: number;
    // show
    seasons?: number;
    championshipsCount?: number;
    maxPoints?: number;
    activity?: { status: string; race_id: number; name: string; date: string }[];
    info?: {
        firstRace?: Race;
        lastRace?: Race;
        firstWin?: Race;
        lastWin?: Race;
        winPercentage: number;
        podiums: number;
        podiumPercentage: number;
        withoutPosition: number;
        raking: { driver_id: number; points: number; position: number };
        championships: string[];
    };
    pointsHistory?: { race: string; date: string; points: number }[];
    positionsHistory?: { position: string; times: number }[];
    drivers?: Driver[];
}
