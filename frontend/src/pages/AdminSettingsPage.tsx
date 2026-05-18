import GoogleSheetsIntegration from '../components/GoogleSheetsIntegration';

const AdminSettingsPage = () => {
  return (
    <div className="min-h-screen bg-[#f4f4f4]">
      <div className="max-w-5xl mx-auto px-6 py-8 md:py-10">

        {/* Page Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-[#002f4f]">Settings</h1>
          <p className="text-[#245575] mt-1">Security, integrations, and data retention.</p>
        </div>

        {/* Integrations Section */}
        <section>
          <GoogleSheetsIntegration />
        </section>

      </div>
    </div>
  );
};

export default AdminSettingsPage;
