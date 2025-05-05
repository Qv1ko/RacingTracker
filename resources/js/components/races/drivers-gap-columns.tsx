import { type Race } from '@/types';
import { Link } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import FlagIcon from '../ui/flag-icon';

export const columns: ColumnDef<NonNullable<Race['result']>[number]>[] = [
    {
        accessorKey: 'positions',
        header: () => <div className="font-bold">Pos.</div>,
        cell: ({ row }) => {
            return row.index + 1;
        },
    },
    {
        accessorKey: 'drivers',
        header: () => <div className="font-bold">Driver</div>,
        cell: ({ row }) => {
            return (
                <Link href={`/drivers/${row.original.driver.id}`} className="hover:text-primary flex items-center gap-2 font-medium">
                    <FlagIcon nationality={row.original.driver.nationality ? row.original.driver.nationality : 'unknown'} size={16} />{' '}
                    {row.original.driver.name} {row.original.driver.surname}
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
    {
        accessorKey: 'gaps',
        header: () => <div className="font-bold">Gap</div>,
        cell: ({ row }) => {
            return row.original.pointsGap !== 0 && <p>-{row.original.pointsGap.toFixed(3)}</p>;
        },
    },
];
