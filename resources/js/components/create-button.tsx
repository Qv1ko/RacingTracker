import { Plus } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';

export function CreateButton({ item, createRoute }: { item: string; createRoute: string }) {
    return (
        <Button className="cursor-pointer">
            <Link href={route(createRoute)} className="flex items-center gap-2">
                <Plus /> Create {item}
            </Link>
        </Button>
    );
}
