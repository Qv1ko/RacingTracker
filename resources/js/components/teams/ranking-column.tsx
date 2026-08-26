import FlagIcon from "@/components/ui/flag-icon";
import { Team } from "@/types";
import { Link } from "@inertiajs/react";

import { type DataTableColumn } from "@/components/data-table";

export const columns: DataTableColumn<
    NonNullable<{
        position: number;
        team: Team;
        points: number;
        championships: number;
    }[]>[number]
>[] = [
    {
        id: "positions",
        header: () => <div className="font-bold">Pos.</div>,
        accessorFn: (row) => row.position,
        cell: ({ row }) => {
            return row.original.position;
        },
    },
    {
        id: "team",
        header: () => <div className="font-bold">Team</div>,
        accessorFn: (row) => row.team.name,
        cell: ({ row }) => {
            const team = row.original.team;
            return (
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
            );
        },
    },
    {
        accessorKey: "championships",
        header: () => <div className="font-bold">Championships</div>,
        cell: ({ row }) => {
            return <p>{row.original.championships}</p>;
        },
    },
    {
        accessorKey: "points",
        header: () => <div className="font-bold">Points</div>,
        cell: ({ row }) => {
            return <p className="flex items-center gap-2">{row.original.points.toFixed(3)} </p>;
        },
    },
];
