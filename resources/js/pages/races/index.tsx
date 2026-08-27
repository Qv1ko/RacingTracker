import { CreateButton } from "@/components/create-button";
import { DataTable, getColumnKey } from "@/components/data-table";
import { columns as tableColumns } from "@/components/races/columns";
import AppLayout from "@/layouts/app-layout";
import { SharedData, type BreadcrumbItem, type Race } from "@/types";
import { Head, usePage } from "@inertiajs/react";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: "Races",
        href: "/races",
    },
];

export default function Races({ races }: { races: Race[] }) {
    const page = usePage<SharedData>();
    const { auth, season } = page.props;

    let columns = [...tableColumns];

    if (season === "all") {
        columns = columns.filter(
            (column) =>
                getColumnKey(column) !== "date" &&
                getColumnKey(column) !== "second" &&
                getColumnKey(column) !== "third",
        );
    } else {
        columns = columns.filter((column) => getColumnKey(column) !== "date-wy");
    }

    if (!auth.user || races.length === 0) {
        columns = columns.filter((column) => getColumnKey(column) !== "actions");
    }

    const dateSortId = season === "all" ? "date-wy" : "date";

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Races" />
            <div className="container mx-auto px-4 py-8">
                <div className="flex justify-between">
                    {auth.user && <CreateButton item="race" createRoute="races.create" />}
                </div>
                <DataTable
                    columns={columns}
                    data={races}
                    initialSorting={[{ id: dateSortId, desc: true }]}
                />
            </div>
        </AppLayout>
    );
}
