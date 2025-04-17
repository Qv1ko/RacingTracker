'use client';

import { Button } from '@/components/ui/button';
import FlagIcon from '@/components/ui/flag-icon';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { nationalities, statuses } from '@/lib/utils';
import type { Team } from '@/types';
import { Head, useForm } from '@inertiajs/react';

export default function EditTeamForm({ team }: { team: Team }) {
    const { data, setData, processing, put, errors } = useForm({
        id: team.id || '',
        name: team.name || '',
        nationality: team.nationality || '',
        status: Boolean(team.status),
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('teams.update', data));
    };

    return (
        <AppLayout>
            <Head title={`Update ${team.name}`} />
            <div className="bg-card mx-auto my-auto mt-16 w-full max-w-md rounded-sm border p-6 shadow-sm">
                <h2 className="mb-6 text-2xl font-semibold tracking-tight">Edit team</h2>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-2">
                        <label htmlFor="name" className="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Name
                        </label>
                        <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} className="w-full" required />
                        {errors.name && <p className="text-destructive text-sm font-medium">{errors.name}</p>}
                    </div>

                    <div className="mt-6 flex gap-4">
                        <div className="w-50 space-y-2">
                            <label
                                htmlFor="nationality"
                                className="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                            >
                                Nationality
                            </label>
                            <Select value={data.nationality} onValueChange={(value) => setData('nationality', value)}>
                                <SelectTrigger id="nationality" className="w-full">
                                    <SelectValue placeholder="Select nationality" />
                                </SelectTrigger>
                                <SelectContent>
                                    {nationalities.sort().map((nationality) => (
                                        <SelectItem key={nationality} value={nationality}>
                                            <FlagIcon nationality={nationality ? nationality.toString() : 'unknown'} size={16} />
                                            {nationality}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.nationality && <p className="text-destructive text-sm font-medium">{errors.nationality}</p>}
                        </div>
                        <div className="w-50 space-y-2">
                            <label
                                htmlFor="status"
                                className="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                            >
                                Driver status
                            </label>
                            <Select value={data.status.toString()} onValueChange={(value) => setData('status', value === 'true')}>
                                <SelectTrigger id="status" className="w-full">
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    {statuses.sort().map((status) => (
                                        <SelectItem key={status.type} value={status.value.toString()}>
                                            {status.type}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.status && <p className="text-destructive text-sm font-medium">{errors.status}</p>}
                        </div>
                    </div>

                    <div className="mt-6 flex gap-4">
                        <Button type="button" variant="outline" className="flex-1 cursor-pointer" onClick={() => window.history.back()}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing} className="flex-1 cursor-pointer">
                            {processing ? 'Saving...' : 'Update team'}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
