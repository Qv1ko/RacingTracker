import { CreateButton } from '@/components/create-button';
import { DataTable } from '@/components/data-table';
import { columns as tableColumns } from '@/components/races/columns';
import { SelectSeason } from '@/components/select-season';
import AppLayout from '@/layouts/app-layout';
import { SharedData, type BreadcrumbItem, type Race } from '@/types';
import { Head, usePage } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Races',
        href: '/races',
    },
];

export default function Races({ seasons, races }: { seasons: string[]; races: Race[] }) {
    const page = usePage<SharedData>();
    const { auth, season } = page.props;

    let columns = [...tableColumns];

    if (season === 'all') {
        columns = columns.filter((column) => column.accessorKey !== 'dates' && column.accessorKey !== 'seconds' && column.accessorKey !== 'thirds');
    } else {
        columns = columns.filter((column) => column.accessorKey !== 'datesWY');
    }

    if (!auth.user || races.length === 0) {
        columns = columns.filter((column) => column.accessorKey !== 'actions');
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Races" />
            <div className="container mx-auto px-4 py-8">
                <div className="flex justify-between">
                    <SelectSeason seasons={seasons} selectedValue={season ? season.toString() : ''} url={'/races'} />
                    {auth.user && <CreateButton item="race" createRoute="races.create" />}
                </div>
                <DataTable columns={columns} data={races} />
            </div>
        </AppLayout>
    );
}
