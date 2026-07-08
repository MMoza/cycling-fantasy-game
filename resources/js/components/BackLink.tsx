import { Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';

interface BackLinkProps {
    href: string;
    label?: string;
}

export default function BackLink({ href, label }: BackLinkProps) {
    return (
        <Link
            href={href}
            className="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
        >
            <ArrowLeft className="h-4 w-4" />
            {label}
        </Link>
    );
}
