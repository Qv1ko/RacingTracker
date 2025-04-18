import { type Race } from '@/types';
import { Link } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
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
            return <p>{row.original.winner ? row.original.winner.name : ''}</p>;
        },
    },
    {
        accessorKey: 'seconds',
        header: () => <div className="text-bold hidden md:table-cell">2nd</div>,
        cell: ({ row }) => {
            return <p className="hidden md:table-cell">{row.original.second ? row.original.second.name : ''}</p>;
        },
    },
    {
        accessorKey: 'thirds',
        header: () => <div className="text-bold hidden md:table-cell">3rd</div>,
        cell: ({ row }) => {
            return <p className="hidden md:table-cell">{row.original.third ? row.original.third.name : ''}</p>;
        },
    },
    {
        accessorKey: 'betters',
        header: () => <div className="text-bold">Better driver</div>,
        cell: ({ row }) => {
            return <p>{row.original.better ? row.original.better.name : ''}</p>;
        },
    },
    ActionsColumn,
];
