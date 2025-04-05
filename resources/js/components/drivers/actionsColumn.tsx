'use client';

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
import { type DriverData } from '@/types';
import { router } from '@inertiajs/react';
import { ColumnDef } from '@tanstack/react-table';
import { MoreHorizontal, Pencil, Trash } from 'lucide-react';

export const ActionsColumn: ColumnDef<DriverData> = {
    accessorKey: 'actions',
    header: () => <div className="text-bold"></div>,
    cell: ({ row }) => {
        const handleEdit = () => {
            router.get(`/drivers/${row.original.id}/edit`);
        };

        const handleDestroy = () => {
            if (confirm('Are you sure you want to delete this driver?')) {
                router.delete(`/drivers/${row.original.id}`);
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
                    <DropdownMenuLabel>Driver actions</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuGroup>
                        <DropdownMenuItem onClick={handleEdit}>
                            <Pencil /> Update
                        </DropdownMenuItem>
                        <DropdownMenuItem onClick={handleDestroy}>
                            <Trash /> Delete
                        </DropdownMenuItem>
                    </DropdownMenuGroup>
                </DropdownMenuContent>
            </DropdownMenu>
        );
    },
};
