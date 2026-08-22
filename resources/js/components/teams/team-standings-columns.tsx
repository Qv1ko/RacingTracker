import FlagIcon from "@/components/ui/flag-icon";
import { type Race } from "@/types";
import { Link } from "@inertiajs/react";

import { type DataTableColumn } from "@/components/data-table";

export const columns: DataTableColumn<NonNullable<Race["teamStandings"]>[number]>[] = [
    {
        id: "positions",
        header: () => <div className="font-bold">Pos.</div>,
        cell: ({ row }) => {
            return row.index + 1;
        },
    },
    {
        id: "team",
        header: () => <div className="font-bold">Team</div>,
        cell: ({ row }) => {
            return (
                row.original.team && (
                    <Link
                        href={`/teams/${row.original.team.id}`}
                        className="hover:text-primary flex items-center gap-2 font-medium"
                    >
                        <FlagIcon
                            nationality={
                                row.original.team.nationality
                                    ? row.original.team.nationality
                                    : "unknown"
                            }
                            size={16}
                        />{" "}
                        {row.original.team.name}
                    </Link>
                )
            );
        },
    },
    {
        accessorKey: "points",
        header: () => <div className="font-bold">Points</div>,
        cell: ({ row }) => {
            return (
                row.original.team && (
                    <p className="flex items-center gap-2">{row.original.points.toFixed(3)}</p>
                )
            );
        },
    },
    {
        id: "gap",
        header: () => <div className="font-bold">Gap</div>,
        cell: ({ row }) => {
            return (
                row.original.team && row.original.gap !== 0 && <p>{row.original.gap.toFixed(3)}</p>
            );
        },
    },
];
