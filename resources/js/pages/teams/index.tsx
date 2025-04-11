import { CreateButton } from '@/components/create-button';
import { DataTable } from '@/components/data-table';
import { SelectSeason } from '@/components/select-season';
import { columns as tableColumns } from '@/components/teams/columns';
import AppLayout from '@/layouts/app-layout';
import { SharedData, type BreadcrumbItem, type TeamData } from '@/types';
import { Head, usePage } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Teams',
        href: '/teams',
    },
];

export default function Teams({ seasons, teams }: { seasons: string[]; teams: TeamData[] }) {
    const page = usePage<SharedData>();
    const { auth, season } = page.props;

    let columns = [...tableColumns];

    if (season === 'all') {
        columns = columns.filter(
            (column) => column.accessorKey !== 'drivers' && column.accessorKey !== 'second_position' && column.accessorKey !== 'third_position',
        );
    }

    if (!auth.user) {
        columns = columns.filter((column) => column.accessorKey !== 'actions');
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Teams" />
            <div className="container mx-auto px-4 py-8">
                <div className="flex justify-between">
                    <SelectSeason seasons={seasons} url={'/teams'} />
                    {auth.user && <CreateButton item="team" createRoute="teams.create" />}
                </div>
                <DataTable columns={columns} data={teams} />
            </div>
        </AppLayout>
    );
}
