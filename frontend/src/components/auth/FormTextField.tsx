import type { InputHTMLAttributes } from 'react'

type FormTextFieldProps = {
  error?: string
  label: string
} & InputHTMLAttributes<HTMLInputElement>

export function FormTextField({ error, id, label, ...inputProps }: FormTextFieldProps) {
  const errorId = error && id ? `${id}-error` : undefined

  return (
    <div className="mt-8">
      <label className="text-[18px] font-medium text-[#002b49]" htmlFor={id}>
        {label}
      </label>
      <input
        {...inputProps}
        aria-describedby={errorId}
        aria-invalid={Boolean(error)}
        className={`mt-3 h-[52px] w-full rounded-[12px] border bg-transparent px-4 text-[18px] text-[#002b49] outline-none transition focus:ring-2 focus:ring-[#285f82]/20 ${
          error ? 'border-[#b42318]' : 'border-[#cbd8e1]'
        }`}
        id={id}
      />
      {error ? (
        <p className="mt-2 text-sm font-medium text-[#b42318]" id={errorId} role="alert">
          {error}
        </p>
      ) : null}
    </div>
  )
}
