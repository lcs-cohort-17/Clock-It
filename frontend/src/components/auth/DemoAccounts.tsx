export type DemoAccount = {
  email: string
  password: string
  role: 'Admin' | 'Staff'
}

type DemoAccountsProps = {
  accounts: DemoAccount[]
}

export function DemoAccounts({ accounts }: DemoAccountsProps) {
  return (
    <section className="mt-[30px] rounded-[12px] border border-dashed border-[#d3dfe7] px-4 py-4">
      {/* Update this heading if the team replaces demo credentials with another access method. */}
      <h2 className="text-[14px] font-bold uppercase tracking-[0.12em] text-[#315c78]">
        Demo Accounts
      </h2>
      <div className="mt-3 space-y-2 font-mono text-[13px] leading-tight text-[#002b49] sm:text-[14px]">
        {accounts.map((account) => (
          <p key={account.email}>
            {account.email} / {account.password} ({account.role})
          </p>
        ))}
      </div>
    </section>
  )
}
