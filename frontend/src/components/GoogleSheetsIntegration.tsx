import { useState } from 'react';
import { Link2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';

const GoogleSheetsIcon = ({ className }: { className?: string }) => (
  <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" className={className}>
    <path d="M29 0H10C8.3 0 7 1.3 7 3v42c0 1.7 1.3 3 3 3h28c1.7 0 3-1.3 3-3V12L29 0z" fill="#9ACA3C" />
    <path d="M29 0v12h12L29 0z" fill="#78A929" />
    <rect x="14" y="22" width="20" height="2" rx="1" fill="#fff" />
    <rect x="14" y="28" width="20" height="2" rx="1" fill="#fff" />
    <rect x="14" y="34" width="20" height="2" rx="1" fill="#fff" />
    <rect x="14" y="22" width="2" height="14" rx="1" fill="#fff" />
    <rect x="22" y="22" width="2" height="14" rx="1" fill="#fff" />
    <rect x="30" y="22" width="2" height="14" rx="1" fill="#fff" />
  </svg>
);

const GoogleSheetsIntegration = () => {
  const [isConnected, setIsConnected] = useState(false);
  const [showConnectModal, setShowConnectModal] = useState(false);
  const [showDisconnectModal, setShowDisconnectModal] = useState(false);
  const [isConnecting, setIsConnecting] = useState(false);
  const [connectedSheet, setConnectedSheet] = useState('');
  const [connectedSince, setConnectedSince] = useState('');

  const handleConnect = () => {
    setIsConnecting(true);
    // Replace this setTimeout with your real OAuth / API call
    setTimeout(() => {
      setIsConnected(true);
      setIsConnecting(false);
      setShowConnectModal(false);
      setConnectedSheet('attendance_export_2026');
      setConnectedSince(
        new Date().toLocaleDateString('en-GB', {
          day: 'numeric',
          month: 'short',
          year: 'numeric',
        })
      );
    }, 1500);
  };

  const handleDisconnect = () => {
    setIsConnected(false);
    setConnectedSheet('');
    setConnectedSince('');
    setShowDisconnectModal(false);
  };

  return (
    <>
      <Card className="w-full rounded-xl border border-[#cbd7df] bg-white px-7 py-7 text-[#002f4f] shadow-sm">
        <CardHeader className="p-0">
          <div className="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
            <div className="flex items-start gap-5">
              <div className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-[#eef6df] p-3">
                <GoogleSheetsIcon className="h-6 w-6" />
              </div>
              <div>
                <CardTitle className="text-xl font-bold text-[#002f4f]">
                  Google Sheets Integration
                </CardTitle>
                <CardDescription className="mt-2 text-base text-[#245575]">
                  Two-way sync of attendance data with auto field mapping.
                </CardDescription>
              </div>
            </div>

            <Badge
              className={`w-fit shrink-0 select-none rounded-full border px-3 py-1 text-sm font-semibold ${
                isConnected
                  ? 'border-[#9ccf57] bg-[#f1f8e8] text-[#5d9a1b]'
                  : 'border-slate-300 bg-slate-100 text-slate-600'
              }`}
            >
              {isConnected ? 'Connected' : 'Not connected'}
            </Badge>
          </div>
        </CardHeader>

        <CardContent className="p-0 pt-5 sm:pl-[68px]">
          {!isConnected ? (
            <Button
              onClick={() => setShowConnectModal(true)}
              disabled={isConnecting}
              className="h-12 rounded-lg border-0 bg-[#06466b] px-5 text-base font-bold text-white shadow-none hover:bg-[#003957] active:bg-[#002f4f]"
            >
              {isConnecting ? (
                <>
                  <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                  Connecting...
                </>
              ) : (
                <>
                  <Link2 className="mr-2 h-4 w-4" />
                  Connect Google Sheet
                </>
              )}
            </Button>
          ) : (
            <Button
              onClick={() => setShowDisconnectModal(true)}
              className="h-12 rounded-lg border-0 bg-red-700 px-5 text-base font-bold text-white hover:bg-red-800"
            >
              Disconnect
            </Button>
          )}

          {isConnected && (
            <div className="mt-5 border-t border-[#dce5eb] pt-4">
              <div className="grid gap-3 text-sm sm:grid-cols-2 sm:gap-x-6">
                <div>
                  <p className="mb-0.5 text-xs text-[#5b7890]">Connected sheet</p>
                  <p className="font-mono text-[#5d9a1b]">{connectedSheet}</p>
                </div>
                <div>
                  <p className="mb-0.5 text-xs text-[#5b7890]">Connected since</p>
                  <p className="text-[#245575]">{connectedSince}</p>
                </div>
                <div>
                  <p className="mb-0.5 text-xs text-[#5b7890]">Syncing</p>
                  <p className="text-[#245575]">Clock-in &amp; clock-out events</p>
                </div>
                <div>
                  <p className="mb-0.5 text-xs text-[#5b7890]">Last sync</p>
                  <p className="text-[#245575]">Just now</p>
                </div>
              </div>
            </div>
          )}
        </CardContent>
      </Card>

      <Dialog open={showConnectModal} onOpenChange={setShowConnectModal}>
        <DialogContent className="sm:max-w-md border-[#cbd7df] bg-white text-[#002f4f]">
          <DialogHeader>
            <DialogTitle className="flex items-center gap-2 text-[#002f4f]">
              <GoogleSheetsIcon className="h-5 w-5 flex-shrink-0" />
              <span>Connect to Google Sheets</span>
            </DialogTitle>
            <DialogDescription className="text-[#245575]">
              Please review the following before connecting.
            </DialogDescription>
          </DialogHeader>

          <div className="space-y-4">
            <div className="rounded-lg border border-[#dce5eb] bg-[#f8fafb] p-4">
              <h4 className="mb-2 text-sm font-semibold text-[#002f4f]">What will happen:</h4>
              <ul className="list-disc space-y-1.5 pl-5 text-sm text-[#245575]">
                <li>You'll be redirected to Google to authorise access</li>
                <li>A new Google Sheet will be created for attendance data</li>
                <li>All future clock-ins will sync automatically</li>
                <li>You can disconnect at any time from Settings</li>
              </ul>
            </div>

            <div className="rounded-lg border border-[#f1d28b] bg-[#fff8e8] p-4">
              <h4 className="mb-2 text-sm font-semibold text-[#8a5a00]">Permissions requested:</h4>
              <ul className="list-disc space-y-1 pl-5 text-sm text-[#6f4b08]">
                <li>View and manage your Google Sheets</li>
                <li>Create and edit new spreadsheets</li>
                <li>Read attendance data from your sheets</li>
              </ul>
            </div>

            <div className="text-sm text-[#245575]">
              <p className="mb-1 font-medium text-[#002f4f]">Data privacy</p>
              <p>
                We only access sheets created by this integration. Your data is
                encrypted and never shared with third parties.
              </p>
            </div>
          </div>

          <DialogFooter className="gap-2 sm:gap-0">
            <Button
              variant="outline"
              onClick={() => setShowConnectModal(false)}
              className="border-[#cbd7df] bg-white text-[#002f4f] hover:bg-[#f4f4f4]"
            >
              Cancel
            </Button>
            <Button
              onClick={handleConnect}
              disabled={isConnecting}
              className="border-0 bg-[#06466b] text-white hover:bg-[#003957]"
            >
              {isConnecting ? (
                <>
                  <div className="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                  Connecting...
                </>
              ) : (
                'Confirm & Connect'
              )}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <Dialog open={showDisconnectModal} onOpenChange={setShowDisconnectModal}>
        <DialogContent className="sm:max-w-md border-[#cbd7df] bg-white text-[#002f4f]">
          <DialogHeader>
            <DialogTitle className="text-[#002f4f]">Disconnect Google Sheets?</DialogTitle>
            <DialogDescription className="text-[#245575]">
              Are you sure? This will stop all automatic data syncing.
            </DialogDescription>
          </DialogHeader>

          <div className="rounded-lg border border-red-200 bg-red-50 p-4">
            <p className="text-sm text-red-700">
              <span className="font-semibold">Warning:</span> Your existing sheets will not
              be deleted, but new attendance data will no longer sync automatically.
            </p>
          </div>

          <p className="text-sm text-[#245575]">
            You can reconnect at any time by clicking "Connect Google Sheet" again.
          </p>

          <DialogFooter className="gap-2 sm:gap-0">
            <Button
              variant="outline"
              onClick={() => setShowDisconnectModal(false)}
              className="border-[#cbd7df] bg-white text-[#002f4f] hover:bg-[#f4f4f4]"
            >
              Keep connected
            </Button>
            <Button
              onClick={handleDisconnect}
              className="border-0 bg-red-700 text-white hover:bg-red-800"
            >
              Yes, disconnect
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </>
  );
};

export default GoogleSheetsIntegration;
