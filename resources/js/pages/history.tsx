import { DataTable } from "@/components/data-table";
import { columns as driverRankingColumns } from "@/components/drivers/ranking-column";
import { Icon } from "@/components/ui/icon";
import { columns as teamRankingColumns } from "@/components/teams/ranking-column";
import AppLayout from "@/layouts/app-layout";
import { HelmetIconNode } from "@/lib/utils";
import { Driver, Team, type BreadcrumbItem } from "@/types";
import { Head } from "@inertiajs/react";
import { Users } from "lucide-react";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "History",
        href: "/history",
    },
];

export default function History({
    drivers,
    teams,
}: {
    drivers: { position: number; driver: Driver; points: number; championships: number }[];
    teams: { position: number; team: Team; points: number; championships: number }[];
}) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="History" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-sm px-4 py-8">
                <div>
                    <div className="mb-4 flex items-center justify-center gap-2">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full">
                            <Icon iconNode={HelmetIconNode} className="h-8 w-8" />
                        </div>
                        <h3 className="text-xl font-semibold">Driver history</h3>
                    </div>
                    <DataTable
                    columns={driverRankingColumns}
                    data={drivers}
                    initialSorting={[{ id: "positions", desc: false }]}
                />
                </div>
                <div>
                    <div className="mb-4 flex items-center justify-center gap-2">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full">
                            <Users className="h-8 w-8" />
                        </div>
                        <h3 className="text-xl font-semibold">Team history</h3>
                    </div>
                    <DataTable
                    columns={teamRankingColumns}
                    data={teams}
                    initialSorting={[{ id: "positions", desc: false }]}
                />
                </div>
            </div>
        </AppLayout>
    );
}
