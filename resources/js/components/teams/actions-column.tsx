import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { type Team } from '@/types';
import { router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { MoreHorizontal, Pencil, Trash } from 'lucide-react';

export const ActionsColumn: ColumnDef<Team> = {
    accessorKey: 'actions',
    header: () => <div className="font-bold"></div>,
    cell: ({ row }) => {
        const handleEdit = (id: number) => {
            router.get(route('teams.edit', id));
        };

        const handleDestroy = (id: number) => {
            if (confirm(`Are you sure you want to delete the ${row.original.name} team?`)) {
                router.delete(route('teams.destroy', id));
            }
        };

        return (
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" className="h-8 w-8 p-0">
                        <span className="sr-only">Open action menu</span>
                        <MoreHorizontal />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent className="w-auto">
                    <DropdownMenuLabel>Team actions</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuGroup>
                        <DropdownMenuItem onClick={() => handleEdit(row.original.id)} className="cursor-pointer">
                            <Pencil /> Update
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={() => handleDestroy(row.original.id)} className="cursor-pointer">
                            <Trash /> Delete
                        </DropdownMenuItem>
                    </DropdownMenuGroup>
                </DropdownMenuContent>
            </DropdownMenu>
        );
    },
};
