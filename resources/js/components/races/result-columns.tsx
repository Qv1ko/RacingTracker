import { type Race } from "@/types";
import { Link } from "@inertiajs/react";
import FlagIcon from "../ui/flag-icon";
import { formatNumber } from "@/lib/utils";

import { type DataTableColumn } from "@/components/data-table";

export const columns: DataTableColumn<NonNullable<Race["result"]>[number]>[] = [
    {
        id: "positions",
        header: () => <div className="font-bold">Pos.</div>,
        accessorFn: (row) => row.position,
        cell: ({ row }) => {
            return row.original.position;
        },
    },
    {
        id: "driver",
        header: () => <div className="font-bold">Driver</div>,
        accessorFn: (row) => `${row.driver.name} ${row.driver.surname}`,
        cell: ({ row }) => {
            const driver = row.original.driver;
            return (
                <Link
                    href={`/drivers/${driver.id}`}
                    className="hover:text-primary flex items-center gap-2 font-medium"
                >
                    <FlagIcon
                        nationality={driver.nationality ? driver.nationality : "unknown"}
                        size={16}
                    />{" "}
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
        id: "team",
        header: () => <div className="font-bold">Team</div>,
        accessorFn: (row) => row.team?.name ?? "",
        cell: ({ row }) => {
            const team = row.original.team;
            return (
                team && (
                    <Link
                        href={`/teams/${team.id}`}
                        className="hover:text-primary flex items-center gap-2 font-medium"
                    >
                        <FlagIcon
                            nationality={team.nationality ? team.nationality : "unknown"}
                            size={16}
                        />{" "}
                        {team.name}
                    </Link>
                )
            );
        },
    },
    {
        accessorKey: "points",
        header: () => <div className="hidden font-bold md:table-cell">Driver points</div>,
        cell: ({ row }) => {
            return (
                <p className="hidden items-center gap-2 md:flex">
                    {formatNumber(row.original.points)}{" "}
                    <span
                        className={`flex items-center text-sm ${row.original.pointsDiff > 0 ? "text-green-600" : row.original.pointsDiff < 0 ? "text-red-500" : ""}`}
                    >
                        ({row.original.pointsDiff > 0 && <span>+</span>}
                        {formatNumber(row.original.pointsDiff)})
                    </span>
                </p>
            );
        },
    },
    {
        accessorKey: "teamPoints",
        header: () => <div className="hidden font-bold md:table-cell">Team points</div>,
        cell: ({ row }) => {
            return (
                row.original.team && (
                    <p className="hidden items-center gap-2 md:flex">
                        {formatNumber(row.original.teamPoints)}
                        <span
                            className={`flex items-center text-sm ${row.original.teamPointsDiff > 0 ? "text-green-600" : row.original.teamPointsDiff < 0 ? "text-red-500" : ""}`}
                        >
                            ({row.original.teamPointsDiff > 0 && <span>+</span>}
                            {formatNumber(row.original.teamPointsDiff)})
                        </span>
                    </p>
                )
            );
        },
    },
];
