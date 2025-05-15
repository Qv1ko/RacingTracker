import FlagIcon from '@/components/ui/flag-icon';
import { Team } from '@/types';
import { Link } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';

export const columns: ColumnDef<NonNullable<{ position: number; team: Team; points: number }[]>[number]>[] = [
    {
        accessorKey: 'positions',
        header: () => <div className="font-bold">Pos.</div>,
        cell: ({ row }) => {
            return row.original.position;
        },
    },
    {
        accessorKey: 'teams',
        header: () => <div className="font-bold">Team</div>,
        cell: ({ row }) => {
            const team = row.original.team;
            return (
                <Link href={`/teams/${team.id}`} className="hover:text-primary flex items-center gap-2 font-medium">
                    <FlagIcon nationality={team.nationality ? team.nationality : 'unknown'} size={16} /> {team.name}
                </Link>
            );
        },
    },
    {
        accessorKey: 'points',
        header: () => <div className="font-bold">Points</div>,
        cell: ({ row }) => {
            return <p className="flex items-center gap-2">{row.original.points.toFixed(3)} </p>;
        },
    },
];
