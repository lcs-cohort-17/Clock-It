import { Wifi, WifiOff } from 'lucide-react';
import type { ConnectionStatus } from '../../types/auth';

interface Props {
  status: ConnectionStatus;
}

export default function InternetStatus({ status }: Props) {
  const statusConfig = {
    online: { icon: Wifi, color: 'text-green-500', text: 'Connected' },
    offline: { icon: WifiOff, color: 'text-red-500', text: 'No Internet' },
    checking: { icon: Wifi, color: 'text-yellow-500 animate-pulse', text: 'Checking...' },
  };

  const { icon: Icon, color, text } = statusConfig[status];

  return (
    <div className="flex items-center gap-2 text-sm">
      <Icon className={`h-4 w-4 ${color}`} />
      {/* EDIT: Change the status text labels if needed */}
      <span className={`font-medium ${color}`}>{text}</span>
    </div>
  );
}
