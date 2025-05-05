import { DataTable } from '@/components/data-table';
import { columns as driversGapColumns } from '@/components/races/drivers-gap-columns';
import { columns as resultColumns } from '@/components/races/result-columns';
import { columns as teamsGapColumns } from '@/components/races/teams-gap-columns';
import AppLayout from '@/layouts/app-layout';
import { Race } from '@/types';
import { Head } from '@inertiajs/react';
import { Flag } from 'lucide-react';

export default function Races({ race }: { race: Race }) {
    console.log(race.result);
    return (
        <AppLayout>
            <Head title="Race" />
            <div className="container mx-auto px-4 py-8">
                <div className="flex justify-center">
                    <div className="mb-8 flex flex-col items-center gap-4 sm:flex-row sm:items-center sm:justify-start">
                        <div className="flex h-16 w-16 items-center justify-center">
                            <Flag className="h-12 w-12" />
                        </div>
                        <div className="text-center sm:text-left">
                            <h2 className="text-2xl font-bold">{race.name}</h2>
                            <div className="flex items-center gap-2">
                                <p>{new Date(race.date).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })}</p>
                            </div>
                        </div>
                    </div>
                </div>
                {race.result && <DataTable columns={resultColumns} data={race.result} />}
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                    {race.result && <DataTable columns={driversGapColumns} data={[...race.result].sort((a, b) => b.points - a.points)} />}
                    {race.result && (
                        <DataTable
                            columns={teamsGapColumns}
                            data={Object.values(
                                (race.result ?? []).reduce(
                                    (acc: Record<number, NonNullable<Race['result']>[number]>, curr: NonNullable<Race['result']>[number]) => {
                                        const teamId = curr.team.id;
                                        if (!acc[teamId] || curr.teamPoints > acc[teamId].teamPoints) {
                                            acc[teamId] = curr;
                                        }
                                        return acc;
                                    },
                                    {},
                                ),
                            ).sort((a, b) => b.teamPoints - a.teamPoints)}
                        />
                    )}
                </div>
                <div className="flex items-center justify-center gap-2">
                    <div className="flex h-12 w-12 items-center justify-center rounded-full">
                        <Flag className="h-8 w-8" />
                    </div>
                    <h3 className="text-xl font-semibold">More {race.name}</h3>
                </div>
            </div>
        </AppLayout>
    );
}
