import FlagIcon from '@/components/ui/flag-icon';
import { type Race } from '@/types';
import { Link } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';

export const columns: ColumnDef<NonNullable<Race['teamStandings']>[number]>[] = [
    {
        accessorKey: 'positions',
        header: () => <div className="font-bold">Pos.</div>,
        cell: ({ row }) => {
            return row.index + 1;
        },
    },
    {
        accessorKey: 'teams',
        header: () => <div className="font-bold">Team</div>,
        cell: ({ row }) => {
            return (
                row.original.team && (
                    <Link href={`/teams/${row.original.team.id}`} className="hover:text-primary flex items-center gap-2 font-medium">
                        <FlagIcon nationality={row.original.team.nationality ? row.original.team.nationality : 'unknown'} size={16} />{' '}
                        {row.original.team.name}
                    </Link>
                )
            );
        },
    },
    {
        accessorKey: 'points',
        header: () => <div className="font-bold">Points</div>,
        cell: ({ row }) => {
            return row.original.team && <p className="flex items-center gap-2">{row.original.points.toFixed(3)}</p>;
        },
    },
    {
        accessorKey: 'gaps',
        header: () => <div className="font-bold">Gap</div>,
        cell: ({ row }) => {
            return row.original.team && row.original.gap !== 0 && <p>{row.original.gap.toFixed(3)}</p>;
        },
    },
];
