import { CreateButton } from '@/components/create-button';
import { DataTable } from '@/components/data-table';
import { columns as tableColumns } from '@/components/drivers/columns';
import { SelectSeason } from '@/components/select-season';
import AppLayout from '@/layouts/app-layout';
import { SharedData, type BreadcrumbItem, type DriverData } from '@/types';
import { Head, usePage } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Drivers',
        href: '/drivers',
    },
];

export default function Drivers({ seasons, drivers }: { seasons: string[]; drivers: DriverData[] }) {
    const page = usePage<SharedData>();
    const { auth, season } = page.props;

    let columns = [...tableColumns];

    if (season === 'all') {
        columns = columns.filter(
            (column) => column.accessorKey !== 'teams' && column.accessorKey !== 'second_position' && column.accessorKey !== 'third_position',
        );
    }

    if (!auth.user) {
        columns = columns.filter((column) => column.accessorKey !== 'actions');
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Drivers" />
            <div className="container mx-auto py-10">
                <div className="flex justify-between">
                    <SelectSeason seasons={seasons} url={'/drivers'} />
                    {auth.user && <CreateButton item="driver" url="/drivers/create" />}
                </div>
                <DataTable columns={columns} data={drivers} />
            </div>
        </AppLayout>
    );
}
