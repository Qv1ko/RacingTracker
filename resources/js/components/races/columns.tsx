import { type Race } from '@/types';
import { Link } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import FlagIcon from '../ui/flag-icon';
import { ActionsColumn } from './actionsColumn';

export const columns: ColumnDef<Race>[] = [
    {
        accessorKey: 'dates',
        header: () => <div className="text-bold">Date</div>,
        cell: ({ row }) => {
            return new Date(row.original.date).toLocaleDateString('en-GB', { month: 'short', day: '2-digit' });
        },
    },
    {
        accessorKey: 'datesWY',
        header: () => <div className="text-bold">Date</div>,
        cell: ({ row }) => {
            return new Date(row.original.date).toLocaleDateString('en-GB', { month: 'short', day: '2-digit', year: 'numeric' });
        },
    },
    {
        accessorKey: 'races',
        header: () => <div className="text-bold">Race</div>,
        cell: ({ row }) => {
            return (
                <Link href={`/races/${row.original.id}`} className="hover:text-primary flex items-center gap-2">
                    {row.original.name}
                </Link>
            );
        },
    },
    {
        accessorKey: 'winners',
        header: () => <div className="text-bold">Winner</div>,
        cell: ({ row }) => {
            const winner = row.original.winner ? row.original.winner : null;
            return (
                winner && (
                    <Link href={winner.id ? `/drivers/${winner.id}` : ''} className="hover:text-primary flex items-center gap-2">
                        {winner.nationality ? <FlagIcon nationality={winner.nationality} size={16} /> : null}
                        {winner.name[0].toUpperCase()} {winner.surname}
                    </Link>
                )
            );
        },
    },
    {
        accessorKey: 'seconds',
        header: () => <div className="text-bold hidden md:table-cell">2nd</div>,
        cell: ({ row }) => {
            const second = row.original.second ? row.original.second : undefined;
            return (
                second && (
                    <Link href={second.id ? `/drivers/${second.id}` : ''} className="hover:text-primary flex items-center gap-2">
                        {second.nationality ? <FlagIcon nationality={second.nationality} size={16} /> : null}
                        {second.name[0].toUpperCase()} {second.surname}
                    </Link>
                )
            );
        },
    },
    {
        accessorKey: 'thirds',
        header: () => <div className="text-bold hidden md:table-cell">3rd</div>,
        cell: ({ row }) => {
            const third = row.original.third ? row.original.third : undefined;
            return (
                third && (
                    <Link href={third.id ? `/drivers/${third.id}` : ''} className="hover:text-primary flex items-center gap-2">
                        {third.nationality ? <FlagIcon nationality={third.nationality} size={16} /> : null}
                        {third.name[0].toUpperCase()} {third.surname}
                    </Link>
                )
            );
        },
    },
    {
        accessorKey: 'betters',
        header: () => <div className="text-bold">Better driver</div>,
        cell: ({ row }) => {
            const better = row.original.better ? row.original.better : null;
            return (
                better && (
                    <Link href={better.id ? `/drivers/${better.id}` : ''} className="hover:text-primary flex items-center gap-2">
                        {better.nationality ? <FlagIcon nationality={better.nationality} size={16} /> : null}
                        {better.name[0].toUpperCase()} {better.surname}
                    </Link>
                )
            );
        },
    },
    ActionsColumn,
];
