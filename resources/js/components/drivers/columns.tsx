'use client';

import FlagIcon from '@/components/ui/flag-icon';
import { type DriverData } from '@/types';
import { Link } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { ActionsColumn } from './actionsColumn';

export const columns: ColumnDef<DriverData>[] = [
    {
        accessorKey: 'drivers',
        header: () => <div className="text-bold">Driver</div>,
        cell: ({ row }) => {
            const { nationality, name, surname } = row.original;
            return (
                <Link href={`/drivers/${row.original.id}`} className="hover:text-primary flex items-center gap-2">
                    <FlagIcon nationality={nationality ? nationality.toString() : 'unknown'} size={16} /> {name} {surname}
                </Link>
            );
        },
    },
    {
        accessorKey: 'teams',
        header: () => <div className="text-bold hidden sm:table-cell">Teams</div>,
        cell: ({ row }) => {
            const teams = row.original.teams || [];
            return <p className="hidden sm:table-cell">{teams.join(', ')}</p>;
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
        accessorKey: 'second_position',
        header: () => <div className="text-bold hidden md:table-cell">2nd</div>,
        cell: ({ row }) => {
            const second_position = row.original.second_position || 0;
            return <p className="hidden md:table-cell">{second_position}</p>;
        },
    },
    {
        accessorKey: 'third_position',
        header: () => <div className="text-bold hidden md:table-cell">3rd</div>,
        cell: ({ row }) => {
            const third_position = row.original.third_position || 0;
            return <p className="hidden md:table-cell">{third_position}</p>;
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
