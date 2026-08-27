import { ActionsColumn } from "@/components/teams/actions-column";
import { Badge } from "@/components/ui/badge";
import FlagIcon from "@/components/ui/flag-icon";
import { formatNumber } from "@/lib/utils";
import { type Team } from "@/types";
import { Link } from "@inertiajs/react";

import { type DataTableColumn } from "@/components/data-table";

export const columns: DataTableColumn<Team>[] = [
    {
        id: "team",
        header: () => <div className="font-bold">Team</div>,
        accessorFn: (row) => row.name,
        cell: ({ row }) => {
            const { nationality, name } = row.original;
            return (
                <Link
                    href={`/teams/${row.original.id}`}
                    className="hover:text-primary flex items-center gap-2 font-medium"
                >
                    <FlagIcon
                        nationality={nationality ? nationality.toString() : "unknown"}
                        size={16}
                    />{" "}
                    {name}
                </Link>
            );
        },
    },
    {
        accessorKey: "status",
        header: () => <div className="hidden font-bold sm:table-cell">Status</div>,
        cell: ({ row }) => {
            return <Badge variant="secondary">{row.original.status ? "Active" : "Inactive"}</Badge>;
        },
    },
    {
        accessorKey: "drivers",
        header: () => <div className="hidden font-bold sm:table-cell">Drivers</div>,
        cell: ({ row }) => {
            const drivers = row.original.drivers
                ? row.original.drivers
                      .filter((driver) => driver)
                      .map((driver) => (
                          <Link
                              key={driver.id}
                              href={`/drivers/${driver.id}`}
                              className="hover:text-primary flex items-center gap-2"
                          >
                              <FlagIcon
                                  nationality={
                                      driver.nationality ? driver.nationality.toString() : "unknown"
                                  }
                                  size={16}
                              />{" "}
                              {driver.name[0].toUpperCase()}. {driver.surname}
                          </Link>
                      ))
                : [];
            return <p className="hidden sm:table-cell">{drivers}</p>;
        },
    },
    {
        accessorKey: "wins",
        header: () => <div className="hidden font-bold sm:table-cell">Wins</div>,
        cell: ({ row }) => {
            const wins = row.original.wins || 0;
            return <p className="hidden sm:table-cell">{wins}</p>;
        },
    },
    {
        accessorKey: "second_positions",
        header: () => <div className="hidden font-bold md:table-cell">2nd</div>,
        cell: ({ row }) => {
            const second_positions = row.original.second_positions || 0;
            return <p className="hidden md:table-cell">{second_positions}</p>;
        },
    },
    {
        accessorKey: "third_positions",
        header: () => <div className="hidden font-bold md:table-cell">3rd</div>,
        cell: ({ row }) => {
            const third_positions = row.original.third_positions || 0;
            return <p className="hidden md:table-cell">{third_positions}</p>;
        },
    },
    {
        accessorKey: "points",
        header: () => <div className="font-bold">Points</div>,
        cell: ({ row }) => {
            const points = row.original.points || null;
            return <p>{points}</p>;
        },
    },
    {
        accessorKey: "gap",
        header: () => <div className="hidden font-bold sm:table-cell">Gap</div>,
        cell: ({ row }) => {
            const gap = row.original.gap ?? 0;
            return <p className="hidden sm:table-cell">{gap !== 0 ? formatNumber(gap) : ""}</p>;
        },
    },
    ActionsColumn,
];
