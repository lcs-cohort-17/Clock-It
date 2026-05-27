import { ConnectionStatus } from "../components/dashboard/ConnectionStatus";
//fix
export default function UserPage() {
  return (
    <div className="p-6">
      <header className="flex justify-end mb-4">
        <ConnectionStatus />
      </header>

      {/* rest of your page */}
    </div>
  );
}
