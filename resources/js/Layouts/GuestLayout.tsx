import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-background px-4 py-12">
            <div className="w-full max-w-sm">
                <div className="mb-8 text-center">
                    <img
                        src="/logo-pedales.png"
                        alt="Pedales"
                        className="mx-auto h-20 w-20 rounded-full object-cover ring-4 ring-border sm:h-24 sm:w-24"
                    />
                </div>

                <div className="space-y-6 sm:rounded-xl sm:border sm:p-8">
                    {children}
                </div>
            </div>
        </div>
    );
}
