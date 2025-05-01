import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import FlagIcon from '@/components/ui/flag-icon';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { otherPositionTypes } from '@/lib/utils';
import { Driver, Team } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { Hash, MoreHorizontal, Plus, Trash, X } from 'lucide-react';
import type { FormEvent } from 'react';

export default function CreateRaceForm({ drivers, teams }: { drivers: Driver[]; teams: Team[] }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        date: new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()),
        result: [
            { position: '1', driver: '', team: '' },
            { position: '2', driver: '', team: '' },
            { position: '3', driver: '', team: '' },
        ],
    });

    const countNumericPositions = data.result.filter((participant) => {
        const pos = participant.position;
        return typeof pos === 'number' || (typeof pos === 'string' && !isNaN(Number(pos)));
    }).length;

    const newParticipant = {
        position: (countNumericPositions + 1).toString(),
        driver: '',
        team: '',
    };

    const changePosition = (index: number) => {
        setData(
            'result',
            data.result
                .map((participation, i) =>
                    i === index
                        ? {
                              ...participation,
                              position: isNumericPosition(participation.position)
                                  ? otherPositionTypes[0].type
                                  : (countNumericPositions + 1).toString(),
                          }
                        : participation,
                )
                .sort(sortResult),
        );
    };

    const removeParticipant = (index: number) => {
        setData('result', correctPositions(data.result.filter((_, i) => i !== index)));
    };

    const sortResult = (a: { position: string; driver: string; team: string }, b: { position: string; driver: string; team: string }) => {
        const posA = a.position;
        const posB = b.position;

        const numA = isNumericPosition(posA) ? Number(posA) : NaN;
        const numB = isNumericPosition(posB) ? Number(posB) : NaN;

        if (!isNaN(numA) && !isNaN(numB)) {
            return numA - numB;
        }

        if (!isNaN(numA)) {
            return -1;
        }

        if (!isNaN(numB)) {
            return 1;
        }

        return 0;
    };

    const correctPositions = (result: { position: string; driver: string; team: string }[]) =>
        result.map((participant, index) =>
            isNumericPosition(participant.position)
                ? {
                      ...participant,
                      position: (index + 1).toString(),
                  }
                : participant,
        );

    const isNumericPosition = (position: string) => typeof position === 'number' || (typeof position === 'string' && !isNaN(Number(position)));

    const isDriverDisabled = (driverId: string) => {
        return data.result.some((participant) => participant.driver === driverId);
    };

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        post(route('races.store'));
    };

    return (
        <AppLayout>
            <Head title="Create race" />
            <div className="bg-card mx-auto my-8 w-full max-w-lg rounded-sm border p-6 shadow-sm">
                <h2 className="mb-6 text-2xl font-semibold tracking-tight">Create new race</h2>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-2">
                        <label htmlFor="name" className="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                            Name
                        </label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            className="w-full"
                            maxLength={50}
                            required
                        />
                        {errors.name && <p className="text-destructive text-sm font-medium">{errors.name}</p>}
                    </div>
                    <div className="flex gap-4 space-y-2">
                        <div className="flex-1">
                            <label
                                htmlFor="date"
                                className="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                            >
                                Date
                            </label>
                            <Input
                                id="date"
                                type="date"
                                value={data.date ? data.date.toISOString().split('T')[0] : new Date().toISOString().split('T')[0]}
                                onChange={(e) => setData('date', e.target.value ? new Date(e.target.value) : new Date())}
                                min="1894-06-22"
                                max={new Date().toISOString().split('T')[0]}
                                required
                            />
                            {errors.date && <p className="text-destructive text-sm font-medium">{errors.date}</p>}
                        </div>
                        <div className="flex-1">
                            <br />
                            <Button
                                type="button"
                                className="w-full cursor-pointer"
                                onClick={() => setData('result', correctPositions(data.result.concat(newParticipant).sort(sortResult)))}
                            >
                                <Plus /> Add participant
                            </Button>
                        </div>
                    </div>

                    <table className="w-full">
                        <thead>
                            <tr>
                                <th className="text-sm leading-none font-medium">Pos</th>
                                <th className="hidden text-sm leading-none font-medium sm:table-cell">Driver</th>
                                <th className="hidden text-sm leading-none font-medium sm:table-cell">Team</th>
                                <th className="table-cell text-sm leading-none font-medium sm:hidden">Participant</th>
                                <th className="w-0"></th>
                            </tr>
                        </thead>
                        <tbody>
                            {data.result.map((participation, i) => (
                                <tr key={'participation-' + i} className="hover:bg-muted/50 mt-2 transition-colors">
                                    <td className="w-8 text-center">
                                        {isNumericPosition(participation.position) ? (
                                            <>{participation.position}</>
                                        ) : (
                                            <Select
                                                value={participation.position}
                                                onValueChange={(value) =>
                                                    setData(
                                                        'result',
                                                        data.result.map((result, index) => (index === i ? { ...result, position: value } : result)),
                                                    )
                                                }
                                            >
                                                <SelectTrigger id={'position-' + i} className="w-full">
                                                    <SelectValue placeholder="Pos" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {otherPositionTypes.sort().map((position) => (
                                                        <SelectItem key={position.type} value={position.type} title={position.description}>
                                                            {position.type}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        )}
                                        {errors['result.' + i + '.position'] && (
                                            <p className="text-destructive text-sm font-medium">{errors['result.' + i + '.position']}</p>
                                        )}
                                    </td>
                                    <td className="block p-2 sm:table-cell">
                                        <Select
                                            value={participation.driver}
                                            onValueChange={(value) =>
                                                setData(
                                                    'result',
                                                    data.result.map((participation, index) =>
                                                        index === i ? { ...participation, driver: value } : participation,
                                                    ),
                                                )
                                            }
                                            disabled={drivers.length === 0}
                                        >
                                            <SelectTrigger id={'driver-' + i} className="w-full">
                                                <SelectValue placeholder="Select driver" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {drivers.sort().map((driver) => (
                                                    <SelectItem
                                                        key={driver.id}
                                                        value={driver.id.toString()}
                                                        disabled={isDriverDisabled(driver.id.toString())}
                                                    >
                                                        <FlagIcon
                                                            nationality={driver.nationality ? driver.nationality.toString() : 'unknown'}
                                                            size={16}
                                                        />{' '}
                                                        {driver.name[0].toUpperCase()}. {driver.surname}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors['result.' + i + '.driver'] && (
                                            <p className="text-destructive text-sm font-medium">{errors['result.' + i + '.driver']}</p>
                                        )}
                                    </td>
                                    <td className="block p-2 sm:table-cell">
                                        <Select
                                            value={participation.team}
                                            onValueChange={(value) =>
                                                setData(
                                                    'result',
                                                    data.result.map((participation, index) =>
                                                        index === i ? { ...participation, team: value } : participation,
                                                    ),
                                                )
                                            }
                                            disabled={teams.length === 0}
                                        >
                                            <SelectTrigger id={'team-' + i} className="w-full">
                                                <SelectValue placeholder="Select team" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {teams.sort().map((team) => (
                                                    <SelectItem key={team.id} value={team.id.toString()}>
                                                        <FlagIcon
                                                            nationality={team.nationality ? team.nationality.toString() : 'unknown'}
                                                            size={16}
                                                        />{' '}
                                                        {team.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors['result.' + i + '.team'] && (
                                            <p className="text-destructive text-sm font-medium">{errors['result.' + i + '.team']}</p>
                                        )}
                                    </td>
                                    <td>
                                        {countNumericPositions !== i + 1 && isNumericPosition(participation.position) ? (
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                className="w-8 cursor-pointer"
                                                title="Delete"
                                                onClick={() => removeParticipant(i)}
                                            >
                                                <X />
                                            </Button>
                                        ) : (
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button type="button" variant="ghost" className="w-8 cursor-pointer" title="Open action menu">
                                                        <MoreHorizontal />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent className="w-auto">
                                                    <DropdownMenuGroup>
                                                        <DropdownMenuItem className="cursor-pointer" title="Delete" onClick={() => changePosition(i)}>
                                                            <Hash /> Other position
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            className="cursor-pointer"
                                                            title="Delete"
                                                            onClick={() => removeParticipant(i)}
                                                        >
                                                            <Trash /> Delete
                                                        </DropdownMenuItem>
                                                    </DropdownMenuGroup>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    <div className="mt-6 flex gap-4">
                        <Button type="button" variant="outline" className="flex-1 cursor-pointer" onClick={() => window.history.back()}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing} className="flex-1 cursor-pointer">
                            {processing ? 'Saving...' : 'Create race'}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
