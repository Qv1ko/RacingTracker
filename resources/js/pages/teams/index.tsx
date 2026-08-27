import { CreateButton } from "@/components/create-button";
import { DataTable, getColumnKey } from "@/components/data-table";
import { SelectSeason } from "@/components/select-season";
import { columns as tableColumns } from "@/components/teams/columns";
import AppLayout from "@/layouts/app-layout";
import { SharedData, type BreadcrumbItem, type Team } from "@/types";
import { Head, usePage } from "@inertiajs/react";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Teams",
        href: "/teams",
    },
];

export default function Teams({ seasons, teams }: { seasons: string[]; teams: Team[] }) {
    const page = usePage<SharedData>();
    const { auth, season } = page.props;

    let columns = [...tableColumns];

    if (season === "all") {
        columns = columns.filter(
            (column) =>
                getColumnKey(column) !== "drivers" &&
                getColumnKey(column) !== "second_positions" &&
                getColumnKey(column) !== "third_positions",
        );
    } else {
        columns = columns.filter((column) => getColumnKey(column) !== "status");
    }

    if (!auth.user || teams.length === 0) {
        columns = columns.filter((column) => getColumnKey(column) !== "actions");
    }

    const leaderPoints = teams.length ? Math.max(...teams.map((t) => t.points ?? 0)) : 0;
    const tableData = teams.map((t) => ({
        ...t,
        gap: leaderPoints - (t.points ?? 0),
    }));

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Teams" />
            <div className="container mx-auto px-4 py-8">
                <div className="flex justify-between">
                    <SelectSeason
                        seasons={seasons}
                        selectedValue={season ? season.toString() : ""}
                        url={"/teams"}
                    />
                    {auth.user && <CreateButton item="team" createRoute="teams.create" />}
                </div>
                <DataTable
                    columns={columns}
                    data={tableData}
                    initialSorting={[{ id: "points", desc: true }]}
                />
            </div>
        </AppLayout>
    );
}
