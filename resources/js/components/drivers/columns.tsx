import { ActionsColumn } from '@/components/drivers/actions-column';
import { Badge } from '@/components/ui/badge';
import FlagIcon from '@/components/ui/flag-icon';
import { type Driver } from '@/types';
import { Link } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';

export const columns: ColumnDef<Driver>[] = [
    {
        accessorKey: 'drivers',
        header: () => <div className="font-bold">Driver</div>,
        cell: ({ row }) => {
            const { name, surname, nationality } = row.original;
            return (
                <Link href={`/drivers/${row.original.id}`} className="hover:text-primary flex items-center gap-2 font-medium">
                    <FlagIcon nationality={nationality ? nationality.toString() : 'unknown'} size={16} />{' '}
                    <span className="hidden md:block">
                        {name} {surname}
                    </span>
                    <span className="block md:hidden">
                        {name[0].toUpperCase()}. {surname}
                    </span>
                </Link>
            );
        },
    },
    {
        accessorKey: 'status',
        header: () => <div className="hidden font-bold sm:table-cell">Status</div>,
        cell: ({ row }) => {
            return (
                <Badge variant="secondary" className="hidden sm:table-cell">
                    {row.original.status ? 'Active' : 'Inactive'}
                </Badge>
            );
        },
    },
    {
        accessorKey: 'teams',
        header: () => <div className="hidden font-bold sm:table-cell">Teams</div>,
        cell: ({ row }) => {
            const teams = row.original.teams
                ? row.original.teams
                      .filter((team) => team)
                      .map((team) => (
                          <Link key={team.id} href={`/teams/${team.id}`} className="hover:text-primary flex items-center gap-2">
                              <FlagIcon nationality={team.nationality ? team.nationality.toString() : 'unknown'} size={16} /> {team.name}
                          </Link>
                      ))
                : [];
            return <p className="hidden sm:table-cell">{teams}</p>;
        },
    },
    {
        accessorKey: 'races',
        header: () => <div className="hidden font-bold sm:table-cell">Races</div>,
        cell: ({ row }) => {
            const races = row.original.races || 0;
            return <p className="hidden sm:table-cell">{races}</p>;
        },
    },
    {
        accessorKey: 'wins',
        header: () => <div className="hidden font-bold sm:table-cell">Wins</div>,
        cell: ({ row }) => {
            const wins = row.original.wins || 0;
            return <p className="hidden sm:table-cell">{wins}</p>;
        },
    },
    {
        accessorKey: 'second_positions',
        header: () => <div className="hidden font-bold md:table-cell">2nd</div>,
        cell: ({ row }) => {
            const second_positions = row.original.second_positions || 0;
            return <p className="hidden md:table-cell">{second_positions}</p>;
        },
    },
    {
        accessorKey: 'third_positions',
        header: () => <div className="hidden font-bold md:table-cell">3rd</div>,
        cell: ({ row }) => {
            const third_positions = row.original.third_positions || 0;
            return <p className="hidden md:table-cell">{third_positions}</p>;
        },
    },
    {
        accessorKey: 'points',
        header: () => <div className="font-bold">Points</div>,
        cell: ({ row }) => {
            const points = row.original.points || '';
            return <p>{points}</p>;
        },
    },
    ActionsColumn,
];
