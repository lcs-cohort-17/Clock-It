import GoogleSheetsIntegration from '../components/AdminSettingsComponents/GoogleSheetsIntegration';
import SecuritySettings from '../components/AdminSettingsComponents/SecuritySettings';
import DataRetention from '../components/DataRetention';

const AdminSettingsPage = () => {
  return (
    <div className="min-h-screen bg-[#f4f4f4]">
      <div className="mx-auto max-w-[1120px] px-0 py-[2px]">
        <div className="space-y-[30px]">
          <section>
            <GoogleSheetsIntegration />
          </section>

          <section>
            <SecuritySettings />
          </section>

          <section>
            <DataRetention />
          </section>
        </div>

      </div>
    </div>
  );
};

export default AdminSettingsPage;
