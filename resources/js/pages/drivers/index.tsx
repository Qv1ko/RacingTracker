import { CreateButton } from "@/components/create-button";
import { DataTable, getColumnKey } from "@/components/data-table";
import { columns as tableColumns } from "@/components/drivers/columns";
import { SelectSeason } from "@/components/select-season";
import AppLayout from "@/layouts/app-layout";
import { SharedData, type BreadcrumbItem, type Driver } from "@/types";
import { Head, usePage } from "@inertiajs/react";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Drivers",
        href: "/drivers",
    },
];

export default function Drivers({ seasons, drivers }: { seasons: string[]; drivers: Driver[] }) {
    const page = usePage<SharedData>();
    const { auth, season } = page.props;

    let columns = [...tableColumns];

    if (season === "all") {
        columns = columns.filter(
            (column) =>
                getColumnKey(column) !== "teams" &&
                getColumnKey(column) !== "second_positions" &&
                getColumnKey(column) !== "third_positions",
        );
    } else {
        columns = columns.filter((column) => getColumnKey(column) !== "status");
    }

    if (!auth.user || drivers.length === 0) {
        columns = columns.filter((column) => getColumnKey(column) !== "actions");
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Drivers" />
            <div className="container mx-auto px-4 py-8">
                <div className="flex justify-between">
                    <SelectSeason
                        seasons={seasons}
                        selectedValue={season ? season.toString() : ""}
                        url={"/drivers"}
                    />
                    {auth.user && <CreateButton item="driver" createRoute="drivers.create" />}
                </div>
                <DataTable columns={columns} data={drivers} />
            </div>
        </AppLayout>
    );
}
