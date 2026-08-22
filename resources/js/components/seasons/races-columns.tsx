import FlagIcon from "@/components/ui/flag-icon";
import { type Race } from "@/types";
import { Link } from "@inertiajs/react";

import { type DataTableColumn } from "@/components/data-table";

export const columns: DataTableColumn<Race>[] = [
    {
        id: "date",
        header: () => <div className="font-bold">Date</div>,
        cell: ({ row }) => {
            return new Date(row.original.date).toLocaleDateString("en-GB", {
                month: "short",
                day: "2-digit",
            });
        },
    },
    {
        id: "race",
        header: () => <div className="font-bold">Race</div>,
        cell: ({ row }) => {
            return (
                <Link
                    href={`/races/${row.original.id}`}
                    className="hover:text-primary flex items-center gap-2 font-medium"
                >
                    {row.original.name}
                </Link>
            );
        },
    },
    {
        id: "winner",
        header: () => <div className="font-bold">Winner</div>,
        cell: ({ row }) => {
            const winner = row.original.winner ? row.original.winner : null;
            const winnerTeam = winner?.team ? winner.team : null;
            return (
                winner && (
                    <div>
                        <Link
                            href={winner.id ? `/drivers/${winner.id}` : ""}
                            className="hover:text-primary flex items-center gap-2"
                        >
                            {winner.nationality ? (
                                <FlagIcon nationality={winner.nationality} size={16} />
                            ) : null}
                            {winner.name[0].toUpperCase()}. {winner.surname}
                        </Link>
                        {winnerTeam && (
                            <Link
                                href={`/teams/${winnerTeam.id}`}
                                className="hover:text-primary flex items-center gap-2"
                            >
                                {winnerTeam.nationality ? (
                                    <FlagIcon nationality={winnerTeam.nationality} size={16} />
                                ) : null}
                                {winnerTeam.name}
                            </Link>
                        )}
                    </div>
                )
            );
        },
    },
    {
        id: "second",
        header: () => <div className="hidden font-bold md:table-cell">2nd</div>,
        cell: ({ row }) => {
            const second = row.original.second ? row.original.second : undefined;
            const secondTeam = second?.team ? second.team : null;
            return (
                second && (
                    <div>
                        <Link
                            href={second.id ? `/drivers/${second.id}` : ""}
                            className="hover:text-primary hidden items-center gap-2 md:flex"
                        >
                            {second.nationality ? (
                                <FlagIcon nationality={second.nationality} size={16} />
                            ) : null}
                            {second.name[0].toUpperCase()}. {second.surname}
                        </Link>
                        {secondTeam && (
                            <Link
                                href={`/teams/${secondTeam.id}`}
                                className="hover:text-primary hidden items-center gap-2 md:flex"
                            >
                                {secondTeam.nationality ? (
                                    <FlagIcon nationality={secondTeam.nationality} size={16} />
                                ) : null}
                                {secondTeam.name}
                            </Link>
                        )}
                    </div>
                )
            );
        },
    },
    {
        id: "third",
        header: () => <div className="hidden font-bold md:table-cell">3rd</div>,
        cell: ({ row }) => {
            const third = row.original.third ? row.original.third : undefined;
            const thirdTeam = third?.team ? third.team : null;
            return (
                third && (
                    <div>
                        <Link
                            href={third.id ? `/drivers/${third.id}` : ""}
                            className="hover:text-primary hidden items-center gap-2 md:flex"
                        >
                            {third.nationality ? (
                                <FlagIcon nationality={third.nationality} size={16} />
                            ) : null}
                            {third.name[0].toUpperCase()}. {third.surname}
                        </Link>
                        {thirdTeam && (
                            <Link
                                href={`/teams/${thirdTeam.id}`}
                                className="hover:text-primary hidden items-center gap-2 md:flex"
                            >
                                {thirdTeam.nationality ? (
                                    <FlagIcon nationality={thirdTeam.nationality} size={16} />
                                ) : null}
                                {thirdTeam.name}
                            </Link>
                        )}
                    </div>
                )
            );
        },
    },
    {
        id: "betterDriver",
        header: () => <div className="hidden font-bold sm:table-cell">Better driver</div>,
        cell: ({ row }) => {
            const betterDriver = row.original.betterDriver ? row.original.betterDriver : null;
            const betterTeam = betterDriver?.team ? betterDriver.team : null;
            return (
                betterDriver && (
                    <div>
                        <Link
                            href={betterDriver.id ? `/drivers/${betterDriver.id}` : ""}
                            className="hover:text-primary hidden items-center gap-2 sm:flex"
                        >
                            {betterDriver.nationality ? (
                                <FlagIcon nationality={betterDriver.nationality} size={16} />
                            ) : null}
                            {betterDriver.name[0].toUpperCase()}. {betterDriver.surname}
                        </Link>
                        {betterTeam && (
                            <Link
                                href={`/teams/${betterTeam.id}`}
                                className="hover:text-primary hidden items-center gap-2 sm:flex"
                            >
                                {betterTeam.nationality ? (
                                    <FlagIcon nationality={betterTeam.nationality} size={16} />
                                ) : null}
                                {betterTeam.name}
                            </Link>
                        )}
                    </div>
                )
            );
        },
    },
    {
        id: "betterTeam",
        header: () => <div className="hidden font-bold lg:table-cell">Better team</div>,
        cell: ({ row }) => {
            const betterTeam = row.original.betterTeam ? row.original.betterTeam : null;
            return (
                betterTeam && (
                    <div>
                        <Link
                            href={`/teams/${betterTeam.id}`}
                            className="hover:text-primary hidden items-center gap-2 sm:flex"
                        >
                            {betterTeam.nationality ? (
                                <FlagIcon nationality={betterTeam.nationality} size={16} />
                            ) : null}
                            {betterTeam.name}
                        </Link>
                    </div>
                )
            );
        },
    },
];
