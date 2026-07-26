import { useEffect, useState } from 'react';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import axios from 'axios';
import { CompetitionEndedModal } from '@/components/CompetitionEndedModal';

interface Notification {
    id: string;
    type: string;
    title: string;
    description: string;
    data: Record<string, any>;
    created_at: string;
}

export function NotificationModal() {
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [currentNotification, setCurrentNotification] = useState<Notification | null>(null);
    const [isOpen, setIsOpen] = useState(false);

    useEffect(() => {
        fetchNotifications();
    }, []);

    const fetchNotifications = async () => {
        try {
            const response = await axios.get('/notifications');
            setNotifications(response.data.notifications);

            if (response.data.notifications.length > 0) {
                setCurrentNotification(response.data.notifications[0]);
                setIsOpen(true);
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    };

    const markAsRead = async (id: string) => {
        try {
            await axios.post(`/notifications/${id}/read`);

            const remaining = notifications.filter(n => n.id !== id);
            setNotifications(remaining);

            if (remaining.length > 0) {
                setCurrentNotification(remaining[0]);
            } else {
                setIsOpen(false);
                setCurrentNotification(null);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    };

    const handleClose = () => {
        if (currentNotification) {
            markAsRead(currentNotification.id);
        }
    };

    if (!currentNotification) return null;

    return (
        <Dialog open={isOpen} onOpenChange={(open) => {
            if (!open) {
                handleClose();
            }
        }}>
            <DialogContent className="sm:max-w-lg p-0 gap-0 overflow-hidden max-h-[90vh] overflow-y-auto" showCloseButton={true}>

                {currentNotification.type === 'competition_ended' && (
                    <CompetitionEndedModal
                        data={currentNotification.data as any}
                        onDismiss={handleClose}
                    />
                )}
            </DialogContent>
        </Dialog>
    );
}
