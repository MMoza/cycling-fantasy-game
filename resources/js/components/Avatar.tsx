import { cn } from '@/lib/utils';

interface AvatarProps {
    user: { name: string; avatar?: string | null };
    size?: 'sm' | 'md' | 'lg';
    className?: string;
}

const sizeClasses = {
    sm: 'h-8 w-8 text-xs',
    md: 'h-10 w-10 text-sm',
    lg: 'h-20 w-20 text-lg',
};

function getInitials(name: string): string {
    return name
        .split(' ')
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();
}

export default function Avatar({ user, size = 'md', className }: AvatarProps) {
    if (user.avatar) {
        return (
            <img
                src={user.avatar}
                alt={user.name}
                className={cn('rounded-full object-cover', sizeClasses[size], className)}
            />
        );
    }

    return (
        <div
            className={cn(
                'flex items-center justify-center rounded-full bg-brand-600 font-medium text-white',
                sizeClasses[size],
                className,
            )}
        >
            {getInitials(user.name)}
        </div>
    );
}
