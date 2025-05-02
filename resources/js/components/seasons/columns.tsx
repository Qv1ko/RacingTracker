import { type Season } from '@/types';
import { Link } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import FlagIcon from '../ui/flag-icon';

export const columns: ColumnDef<Season>[] = [
    {
        accessorKey: 'seasons',
        header: () => <div className="font-bold">Season</div>,
        cell: ({ row }) => {
            return (
                <Link href={`/seasons/${row.original.season}`} className="hover:text-primary flex items-center gap-2 font-medium">
                    {row.original.season}
                </Link>
            );
        },
    },
    {
        accessorKey: 'champion drivers',
        header: () => <div className="font-bold">Champion driver</div>,
        cell: ({ row }) => {
            const drivers = row.original.driverResults;
            const champion = drivers.filter((driver) => driver.position === 1)[0]?.driver;
            return (
                <Link href={champion.id ? `/drivers/${champion.id}` : ''} className="hover:text-primary flex items-center gap-2">
                    {champion.nationality ? <FlagIcon nationality={champion.nationality} size={16} /> : null}
                    {champion.name} {champion.surname}
                </Link>
            );
        },
    },
    {
        accessorKey: 'champion teams',
        header: () => <div className="font-bold">Champion team</div>,
        cell: ({ row }) => {
            const teams = row.original.teamResults;
            const champion = teams?.filter((team) => team.position === 1)[0]?.team;
            return (
                champion && (
                    <Link href={champion.id ? `/teams/${champion.id}` : ''} className="hover:text-primary flex items-center gap-2">
                        {champion.nationality ? <FlagIcon nationality={champion.nationality} size={16} /> : null}
                        {champion.name}
                    </Link>
                )
            );
        },
    },
    {
        accessorKey: 'races',
        header: () => <div className="hidden font-bold sm:table-cell">Races</div>,
        cell: ({ row }) => {
            return <p className="hidden sm:table-cell">{row.original.races}</p>;
        },
    },
    {
        accessorKey: 'drivers',
        header: () => <div className="hidden font-bold sm:table-cell">Drivers</div>,
        cell: ({ row }) => {
            return <p className="hidden sm:table-cell">{row.original.drivers}</p>;
        },
    },
    {
        accessorKey: 'teams',
        header: () => <div className="hidden font-bold sm:table-cell">Teams</div>,
        cell: ({ row }) => {
            return <p className="hidden sm:table-cell">{row.original.teams}</p>;
        },
    },
];
