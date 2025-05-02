import { DataTable } from '@/components/data-table';
import { columns } from '@/components/seasons/columns';
import AppLayout from '@/layouts/app-layout';
import { Season, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Seasons',
        href: '/seasons',
    },
];

export default function Seasons({ seasons }: { seasons: Season[] }) {
    console.log(seasons);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Seasons" />
            <div className="container mx-auto px-4 py-8">
                <DataTable columns={columns} data={seasons} />
            </div>
        </AppLayout>
    );
}
