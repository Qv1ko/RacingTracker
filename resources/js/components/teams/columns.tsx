'use client';

import FlagIcon from '@/components/ui/flag-icon';
import { type Team } from '@/types';
import { Link } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { ActionsColumn } from './actionsColumn';

export const columns: ColumnDef<Team>[] = [
    {
        accessorKey: 'teams',
        header: () => <div className="text-bold">Team</div>,
        cell: ({ row }) => {
            const { nationality, name } = row.original;
            return (
                <Link href={`/teams/${row.original.id}`} className="hover:text-primary flex items-center gap-2">
                    <FlagIcon nationality={nationality ? nationality.toString() : 'unknown'} size={16} /> {name}
                </Link>
            );
        },
    },
    {
        accessorKey: 'drivers',
        header: () => <div className="text-bold hidden sm:table-cell">Drivers</div>,
        cell: ({ row }) => {
            const drivers = row.original.drivers || [];
            return <p className="hidden sm:table-cell">{drivers.join(', ')}</p>;
        },
    },
    {
        accessorKey: 'races',
        header: () => <div className="text-bold hidden sm:table-cell">Races</div>,
        cell: ({ row }) => {
            const races = row.original.races || 0;
            return <p className="hidden sm:table-cell">{races}</p>;
        },
    },
    {
        accessorKey: 'wins',
        header: () => <div className="text-bold hidden sm:table-cell">Wins</div>,
        cell: ({ row }) => {
            const wins = row.original.wins || 0;
            return <p className="hidden sm:table-cell">{wins}</p>;
        },
    },
    {
        accessorKey: 'second_positions',
        header: () => <div className="text-bold hidden md:table-cell">2nd</div>,
        cell: ({ row }) => {
            const second_positions = row.original.second_positions || 0;
            return <p className="hidden md:table-cell">{second_positions}</p>;
        },
    },
    {
        accessorKey: 'third_positions',
        header: () => <div className="text-bold hidden md:table-cell">3rd</div>,
        cell: ({ row }) => {
            const third_positions = row.original.third_positions || 0;
            return <p className="hidden md:table-cell">{third_positions}</p>;
        },
    },
    {
        accessorKey: 'points',
        header: () => <div className="text-bold">Points</div>,
        cell: ({ row }) => {
            const points = row.original.points || 0;
            return <p>{points}</p>;
        },
    },
    ActionsColumn,
];
