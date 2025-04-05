import { Plus } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';

export function CreateButton({ item, url }: { item: string; url: string }) {
    return (
        <Button>
            <Link href={url} className="flex items-center gap-2">
                <Plus /> Create {item}
            </Link>
        </Button>
    );
}
