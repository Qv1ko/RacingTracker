import FlagIcon from "@/components/ui/flag-icon";
import { type Season } from "@/types";
import { Link } from "@inertiajs/react";

import { type DataTableColumn } from "@/components/data-table";

export const columns: DataTableColumn<Season>[] = [
    {
        id: "season",
        header: () => <div className="font-bold">Season</div>,
        cell: ({ row }) => {
            return (
                <Link
                    href={`/seasons/${row.original.season}`}
                    className="hover:text-primary flex items-center gap-2 font-medium"
                >
                    {row.original.season}
                </Link>
            );
        },
    },
    {
        id: "champion-driver",
        header: () => <div className="font-bold">Champion driver</div>,
        cell: ({ row }) => {
            const champion = row.original.championDriver;
            return (
                champion && (
                    <Link
                        href={champion.id ? `/drivers/${champion.id}` : ""}
                        className="hover:text-primary flex items-center gap-2"
                    >
                        {champion.nationality ? (
                            <FlagIcon nationality={champion.nationality} size={16} />
                        ) : null}
                        <span className="hidden md:block">
                            {champion.name} {champion.surname}
                        </span>
                        <span className="block md:hidden">
                            {champion.name[0].toUpperCase()}. {champion.surname}
                        </span>
                    </Link>
                )
            );
        },
    },
    {
        id: "champion-team",
        header: () => <div className="font-bold">Champion team</div>,
        cell: ({ row }) => {
            const champion = row.original.championTeam;
            return (
                champion && (
                    <Link
                        href={champion.id ? `/teams/${champion.id}` : ""}
                        className="hover:text-primary flex items-center gap-2"
                    >
                        {champion.nationality ? (
                            <FlagIcon nationality={champion.nationality} size={16} />
                        ) : null}
                        {champion.name}
                    </Link>
                )
            );
        },
    },
    {
        accessorKey: "races",
        header: () => <div className="hidden font-bold sm:table-cell">Races</div>,
        cell: ({ row }) => {
            return <p className="hidden sm:table-cell">{row.original.racesCount}</p>;
        },
    },
    {
        accessorKey: "driversCount",
        header: () => <div className="hidden font-bold sm:table-cell">Drivers</div>,
        cell: ({ row }) => {
            return <p className="hidden sm:table-cell">{row.original.driversCount}</p>;
        },
    },
    {
        accessorKey: "teamsCount",
        header: () => <div className="hidden font-bold sm:table-cell">Teams</div>,
        cell: ({ row }) => {
            return <p className="hidden sm:table-cell">{row.original.teamsCount}</p>;
        },
    },
];
