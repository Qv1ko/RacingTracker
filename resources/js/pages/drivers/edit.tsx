'use client';

import { Button } from '@/components/ui/button';
import FlagIcon from '@/components/ui/flag-icon';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { nationalities } from '@/lib/utils';
import type { DriverForm } from '@/types';
import { Head, useForm } from '@inertiajs/react';

export default function EditDriverForm({ driver }: { driver: DriverForm }) {
    const { data, setData, processing, put, errors } = useForm({
        id: driver.id || '',
        name: driver.name || '',
        surname: driver.surname || '',
        nationality: driver.nationality || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('drivers.update', data));
    };

    return (
        <AppLayout>
            <Head title={`Update ${driver.name} ${driver.surname}`} />
            <div className="bg-card mx-auto my-auto mt-16 w-full max-w-md rounded-sm border p-6 shadow-sm">
                <h2 className="mb-6 text-2xl font-semibold tracking-tight">Edit driver</h2>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-2">
                        <label htmlFor="name" className="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Name
                        </label>
                        <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} className="w-full" />
                        {errors.name && <p className="text-destructive text-sm font-medium">{errors.name}</p>}
                    </div>

                    <div className="space-y-2">
                        <label
                            htmlFor="surname"
                            className="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            Surname
                        </label>
                        <Input id="surname" value={data.surname} onChange={(e) => setData('surname', e.target.value)} className="w-full" />
                        {errors.surname && <p className="text-destructive text-sm font-medium">{errors.surname}</p>}
                    </div>

                    <div className="space-y-2">
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

                    <div className="mt-6 flex gap-4">
                        <Button type="button" variant="outline" className="flex-1 cursor-pointer" onClick={() => window.history.back()}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing} className="flex-1 cursor-pointer">
                            {processing ? 'Saving...' : 'Update Driver'}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
