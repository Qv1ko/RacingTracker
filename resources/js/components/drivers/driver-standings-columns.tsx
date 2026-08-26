import FlagIcon from "@/components/ui/flag-icon";
import { type Race } from "@/types";
import { Link } from "@inertiajs/react";

import { type DataTableColumn } from "@/components/data-table";

export const columns: DataTableColumn<NonNullable<Race["driverStandings"]>[number]>[] = [
    {
        id: "positions",
        header: () => <div className="font-bold">Pos.</div>,
        enableSorting: false,
        cell: ({ row }) => {
            return row.index + 1;
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
                    <span className="hidden lg:block">
                        {driver.name} {driver.surname}
                    </span>
                    <span className="block lg:hidden">
                        {driver.name[0].toUpperCase()}. {driver.surname}
                    </span>{" "}
                </Link>
            );
        },
    },
    {
        accessorKey: "points",
        header: () => <div className="font-bold">Points</div>,
        cell: ({ row }) => {
            return <p className="flex items-center gap-2">{row.original.points.toFixed(3)} </p>;
        },
    },
    {
        id: "gap",
        header: () => <div className="font-bold">Gap</div>,
        accessorFn: (row) => row.gap,
        cell: ({ row }) => {
            return row.original.gap !== 0 && <p>{row.original.gap.toFixed(3)}</p>;
        },
    },
];
