import FlagIcon from '@/components/ui/flag-icon';
import AppLayout from '@/layouts/app-layout';
import { Team, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Separator } from '@radix-ui/react-separator';
import { User } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Team profile',
        href: '/teams',
    },
];

export default function Teams({ team }: { team: Team }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Team" />
            <div className="flex justify-center px-4 py-8">
                <div className="mb-4 flex flex-col items-center gap-4 sm:flex-row sm:items-center sm:justify-start">
                    <div className="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                        <User className="h-8 w-8 text-slate-500" />
                    </div>
                    <div className="text-center sm:text-left">
                        <h2 className="text-2xl font-bold">{team.name}</h2>
                        <div className="flex items-center gap-2">
                            <FlagIcon nationality={team.nationality} size={24} />
                            <p className="text-slate-500">{team.nationality}</p>
                        </div>
                    </div>
                </div>

                <Separator className="my-4" />

                <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                    {/* {stats.map((stat, index) => (
                        <Card key={index} className="overflow-hidden">
                            <CardContent className="p-4">
                                <p className="text-sm text-slate-500">{stat.label}</p>
                                <p className="text-xl font-bold">{stat.value}</p>
                            </CardContent>
                        </Card>
                    ))} */}
                </div>
            </div>
        </AppLayout>
    );
}
