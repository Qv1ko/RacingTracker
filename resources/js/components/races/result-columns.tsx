import { type Race } from '@/types';
import { Link } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import FlagIcon from '../ui/flag-icon';

export const columns: ColumnDef<NonNullable<Race['result']>[number]>[] = [
    {
        accessorKey: 'positions',
        header: () => <div className="font-bold">Pos.</div>,
        cell: ({ row }) => {
            return row.original.position;
        },
    },
    {
        accessorKey: 'drivers',
        header: () => <div className="font-bold">Driver</div>,
        cell: ({ row }) => {
            const driver = row.original.driver;
            return (
                <Link href={`/drivers/${driver.id}`} className="hover:text-primary flex items-center gap-2 font-medium">
                    <FlagIcon nationality={driver.nationality ? driver.nationality : 'unknown'} size={16} />{' '}
                    <span className="hidden md:block">
                        {driver.name} {driver.surname}
                    </span>
                    <span className="block md:hidden">
                        {driver.name[0].toUpperCase()}. {driver.surname}
                    </span>
                </Link>
            );
        },
    },
    {
        accessorKey: 'teams',
        header: () => <div className="font-bold">Team</div>,
        cell: ({ row }) => {
            const team = row.original.team;
            return (
                team && (
                    <Link href={`/teams/${team.id}`} className="hover:text-primary flex items-center gap-2 font-medium">
                        <FlagIcon nationality={team.nationality ? team.nationality : 'unknown'} size={16} /> {team.name}
                    </Link>
                )
            );
        },
    },
    {
        accessorKey: 'points',
        header: () => <div className="hidden font-bold md:table-cell">Driver points</div>,
        cell: ({ row }) => {
            return (
                <p className="hidden items-center gap-2 md:flex">
                    {row.original.points.toFixed(3)}{' '}
                    <span
                        className={`flex items-center text-sm ${row.original.pointsDiff > 0 ? 'text-green-600' : row.original.pointsDiff < 0 ? 'text-red-500' : ''}`}
                    >
                        ({row.original.pointsDiff > 0 && <span>+</span>}
                        {row.original.pointsDiff.toFixed(3)})
                    </span>
                </p>
            );
        },
    },
    {
        accessorKey: 'teamPoints',
        header: () => <div className="hidden font-bold md:table-cell">Team points</div>,
        cell: ({ row }) => {
            return (
                row.original.team && (
                    <p className="hidden items-center gap-2 md:flex">
                        {row.original.teamPoints.toFixed(3)}
                        <span
                            className={`flex items-center text-sm ${row.original.teamPointsDiff > 0 ? 'text-green-600' : row.original.teamPointsDiff < 0 ? 'text-red-500' : ''}`}
                        >
                            ({row.original.teamPointsDiff > 0 && <span>+</span>}
                            {row.original.teamPointsDiff.toFixed(3)})
                        </span>
                    </p>
                )
            );
        },
    },
];
