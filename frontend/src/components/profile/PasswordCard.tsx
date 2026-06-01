// import { useState, type FormEvent } from 'react'
// import { Eye, EyeOff } from 'lucide-react'

// /* =========================
//    SectionHeader Component
// ========================= */

// type SectionHeaderProps = {
//   title: string
//   subtitle: string
// }

// const SectionHeader = ({
//   title,
//   subtitle,
// }: SectionHeaderProps) => {
//   return (
//     <div>
//       <h2
//         id="password-section-heading"
//         className="text-2xl font-semibold text-[#0A3A5A]"
//       >
//         {title}
//       </h2>

//       <p className="text-gray-500 mt-1">
//         {subtitle}
//       </p>
//     </div>
//   )
// }

// /* =========================
//    PasswordInput Component
// ========================= */

// type PasswordInputProps = {
//   label: string
//   id: string
//   placeholder: string
//   value: string
//   onChange: (
//     e: React.ChangeEvent<HTMLInputElement>
//   ) => void
//   testId?: string
// }

// const PasswordInput = ({
//   label,
//   id,
//   placeholder,
//   value,
//   onChange,
//   testId,
// }: PasswordInputProps) => {
//   const [showPassword, setShowPassword] =
//     useState(false)

//   return (
//     <div>
//       <label
//         htmlFor={id}
//         className="block text-sm font-medium text-gray-700 mb-2"
//       >
//         {label}
//       </label>

//       <div className="relative w-full max-w-sm">
//         <input
//           id={id}
//           name={id}
//           type={showPassword ? 'text' : 'password'}
//           placeholder={placeholder}
//           value={value}
//           onChange={onChange}
//           data-testid={testId ?? `${id}-input`}
//           className="w-full rounded-xl border border-gray-300 px-3 py-2 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
//         />

//         <button
//           type="button"
//           onClick={() =>
//             setShowPassword(!showPassword)
//           }
//           aria-label={showPassword ? 'Hide password' : 'Show password'}
//           aria-pressed={showPassword}
//           aria-controls={id}
//           title={showPassword ? 'Hide password' : 'Show password'}
//           data-testid={`${id}-toggle-button`}
//           className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
//         >
//           {showPassword ? (
//             <EyeOff size={18} aria-hidden="true" />
//           ) : (
//             <Eye size={18} aria-hidden="true" />
//           )}
//         </button>
//       </div>
//     </div>
//   )
// }

// /* =========================
//    PasswordSection Component
// ========================= */

// const PasswordSection = () => {
//   const [currentPassword, setCurrentPassword] =
//     useState('')

//   const [newPassword, setNewPassword] =
//     useState('')

//   const [confirmPassword, setConfirmPassword] =
//     useState('')

//   // Validation states
//   const [error, setError] = useState('')
//   const [success, setSuccess] = useState('')

//   // Password checks
//   const hasUpperCase = /[A-Z]/.test(newPassword)
//   const hasLowerCase = /[a-z]/.test(newPassword)
//   const hasNumber = /[0-9]/.test(newPassword)
//   const hasSpecialChar =
//     /[^A-Za-z0-9]/.test(newPassword)

//   const hasMinLength =
//     newPassword.length >= 8

//   // Password strength
//   const passwordStrength = [
//     hasUpperCase,
//     hasLowerCase,
//     hasNumber,
//     hasSpecialChar,
//     hasMinLength,
//   ].filter(Boolean).length

//   const handleSubmit = (e: FormEvent) => {
//     e.preventDefault()

//     setError('')
//     setSuccess('')

//     if (
//       !currentPassword ||
//       !newPassword ||
//       !confirmPassword
//     ) {
//       setError('Please fill in all fields')
//       return
//     }

//     if (
//       !hasUpperCase ||
//       !hasLowerCase ||
//       !hasNumber ||
//       !hasSpecialChar ||
//       !hasMinLength
//     ) {
//       setError(
//         'Password must include uppercase, lowercase, number, special character and minimum 8 characters'
//       )
//       return
//     }

//     if (newPassword !== confirmPassword) {
//       setError('Passwords do not match')
//       return
//     }

//     setSuccess('Password updated successfully')

//     // Clear fields
//     setCurrentPassword('')
//     setNewPassword('')
//     setConfirmPassword('')
//   }

//   return (
//     <section className="w-full max-w-4xl bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6">
//       <SectionHeader
//         title="Change password"
//         subtitle="Update your password securely."
//       />

//       <form
//         onSubmit={handleSubmit}
//         className="mt-6 grid gap-4"
//         aria-labelledby="password-section-heading"
//         data-testid="password-form"
//       >
//         <PasswordInput
//           id="current-password"
//           label="Current password"
//           placeholder="Enter current password"
//           value={currentPassword}
//           onChange={(e) =>
//             setCurrentPassword(e.target.value)
//           }
//         />

//         {/* New Password */}
//         <div>
//           <PasswordInput
//             id="new-password"
//             label="New password"
//             placeholder="Enter new password"
//             value={newPassword}
//             onChange={(e) =>
//               setNewPassword(e.target.value)
//             }
//           />

//           {/* Password Strength Text */}
//           {newPassword.length > 0 && (
//             <div
//             className="mt-3 max-w-sm"
//             data-testid="password-strength"
//             role="status"
//             aria-live="polite"
//           >
//               <p
//                 className={`text-sm font-medium ${
//                   passwordStrength <= 2
//                     ? 'text-red-500'
//                     : passwordStrength <= 4
//                     ? 'text-yellow-500'
//                     : 'text-green-600'
//                 }`}
//               >
//                 Password strength:{' '}
//                 {passwordStrength <= 2
//                   ? 'Weak'
//                   : passwordStrength <= 4
//                   ? 'Medium'
//                   : 'Strong'}
//               </p>
//             </div>
//           )}
//         </div>

//         <PasswordInput
//           id="confirm-password"
//           label="Confirm new password"
//           placeholder="Confirm new password"
//           value={confirmPassword}
//           onChange={(e) =>
//             setConfirmPassword(e.target.value)
//           }
//         />

//         {/* Error Message */}
//         {error && (
//           <p
//             className="text-sm font-medium text-red-500"
//             role="alert"
//             data-testid="password-error"
//           >
//             {error}
//           </p>
//         )}

//         {/* Success Message */}
//         {success && (
//           <p
//             className="text-sm font-medium text-green-600"
//             role="status"
//             data-testid="password-success"
//           >
//             {success}
//           </p>
//         )}

//         <button
//           type="submit"
//           data-testid="password-submit"
//           className="inline-flex w-fit rounded-xl bg-[#093B5D] px-5 py-2 text-white font-medium transition hover:bg-[#082F49] self-start"
//         >
//           Update password
//         </button>
//       </form>
//     </section>
//   )
// }

// export default PasswordSection;




import { useState, type FormEvent } from 'react'
import { Eye, EyeOff } from 'lucide-react'

/* =========================
   SectionHeader Component
========================= */

type SectionHeaderProps = {
  title: string
  subtitle: string
}

const SectionHeader = ({
  title,
  subtitle,
}: SectionHeaderProps) => {
  return (
    <div>
      <h2
        id="password-section-heading"
        className="text-2xl font-semibold text-[#0A3A5A]"
      >
        {title}
      </h2>

      <p className="text-gray-500 mt-1">
        {subtitle}
      </p>
    </div>
  )
}

/* =========================
   PasswordInput Component
========================= */

type PasswordInputProps = {
  label: string
  id: string
  placeholder: string
  value: string
  onChange: (
    e: React.ChangeEvent<HTMLInputElement>
  ) => void
  testId?: string
}

const PasswordInput = ({
  label,
  id,
  placeholder,
  value,
  onChange,
  testId,
}: PasswordInputProps) => {
  const [showPassword, setShowPassword] =
    useState(false)

  return (
    <div>
      <label
        htmlFor={id}
        className="block text-sm font-medium text-gray-700 mb-2"
      >
        {label}
      </label>

      <div className="relative w-full max-w-sm">
        <input
          id={id}
          name={id}
          type={showPassword ? 'text' : 'password'}
          placeholder={placeholder}
          value={value}
          onChange={onChange}
          data-testid={testId ?? `${id}-input`}
          className="
            w-full rounded-xl border border-gray-300
            px-3 py-2 pr-10 text-sm text-gray-900
            placeholder:text-gray-400
            focus:outline-none focus:ring-2 focus:ring-blue-500

            /* Hide Edge password reveal icon */
            [&::-ms-reveal]:hidden
            [&::-ms-clear]:hidden
          "
        />

        <button
          type="button"
          onClick={() =>
            setShowPassword(!showPassword)
          }
          aria-label={
            showPassword
              ? 'Hide password'
              : 'Show password'
          }
          aria-pressed={showPassword}
          aria-controls={id}
          title={
            showPassword
              ? 'Hide password'
              : 'Show password'
          }
          data-testid={`${id}-toggle-button`}
          className="
            absolute right-3 top-1/2
            -translate-y-1/2
            text-gray-500 hover:text-gray-700
          "
        >
          {showPassword ? (
            <EyeOff
              size={18}
              aria-hidden="true"
            />
          ) : (
            <Eye
              size={18}
              aria-hidden="true"
            />
          )}
        </button>
      </div>
    </div>
  )
}

/* =========================
   PasswordSection Component
========================= */

const PasswordSection = () => {
  const [currentPassword, setCurrentPassword] =
    useState('')

  const [newPassword, setNewPassword] =
    useState('')

  const [confirmPassword, setConfirmPassword] =
    useState('')

  // Validation states
  const [error, setError] = useState('')
  const [success, setSuccess] = useState('')

  // Password checks
  const hasUpperCase = /[A-Z]/.test(
    newPassword
  )

  const hasLowerCase = /[a-z]/.test(
    newPassword
  )

  const hasNumber = /[0-9]/.test(
    newPassword
  )

  const hasSpecialChar =
    /[^A-Za-z0-9]/.test(newPassword)

  const hasMinLength =
    newPassword.length >= 8

  // Password strength
  const passwordStrength = [
    hasUpperCase,
    hasLowerCase,
    hasNumber,
    hasSpecialChar,
    hasMinLength,
  ].filter(Boolean).length

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault()

    setError('')
    setSuccess('')

    if (
      !currentPassword ||
      !newPassword ||
      !confirmPassword
    ) {
      setError('Please fill in all fields')
      return
    }

    if (
      !hasUpperCase ||
      !hasLowerCase ||
      !hasNumber ||
      !hasSpecialChar ||
      !hasMinLength
    ) {
      setError(
        'Password must include uppercase, lowercase, number, special character and minimum 8 characters'
      )
      return
    }

    if (newPassword !== confirmPassword) {
      setError('Passwords do not match')
      return
    }

    setSuccess(
      'Password updated successfully'
    )

    // Clear fields
    setCurrentPassword('')
    setNewPassword('')
    setConfirmPassword('')
  }

  return (
    <section className="w-full max-w-4xl bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6">
      <SectionHeader
        title="Change password"
        subtitle="Update your password securely."
      />

      <form
        onSubmit={handleSubmit}
        className="mt-6 grid gap-4"
        aria-labelledby="password-section-heading"
        data-testid="password-form"
      >
        <PasswordInput
          id="current-password"
          label="Current password"
          placeholder="Enter current password"
          value={currentPassword}
          onChange={(e) =>
            setCurrentPassword(e.target.value)
          }
        />

        {/* New Password */}
        <div>
          <PasswordInput
            id="new-password"
            label="New password"
            placeholder="Enter new password"
            value={newPassword}
            onChange={(e) =>
              setNewPassword(e.target.value)
            }
          />

          {/* Password Strength Text */}
          {newPassword.length > 0 && (
            <div
              className="mt-3 max-w-sm"
              data-testid="password-strength"
              role="status"
              aria-live="polite"
            >
              <p
                className={`text-sm font-medium ${
                  passwordStrength <= 2
                    ? 'text-red-500'
                    : passwordStrength <= 4
                    ? 'text-yellow-500'
                    : 'text-green-600'
                }`}
              >
                Password strength:{' '}
                {passwordStrength <= 2
                  ? 'Weak'
                  : passwordStrength <= 4
                  ? 'Medium'
                  : 'Strong'}
              </p>
            </div>
          )}
        </div>

        <PasswordInput
          id="confirm-password"
          label="Confirm new password"
          placeholder="Confirm new password"
          value={confirmPassword}
          onChange={(e) =>
            setConfirmPassword(e.target.value)
          }
        />

        {/* Error Message */}
        {error && (
          <p
            className="text-sm font-medium text-red-500"
            role="alert"
            data-testid="password-error"
          >
            {error}
          </p>
        )}

        {/* Success Message */}
        {success && (
          <p
            className="text-sm font-medium text-green-600"
            role="status"
            data-testid="password-success"
          >
            {success}
          </p>
        )}

        <button
          type="submit"
          data-testid="password-submit"
          className="inline-flex w-fit rounded-xl bg-[#093B5D] px-5 py-2 text-white font-medium transition hover:bg-[#082F49] self-start"
        >
          Update password
        </button>
      </form>
    </section>
  )
}

export default PasswordSection