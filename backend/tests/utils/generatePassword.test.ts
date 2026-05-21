import { describe, it, expect } from 'vitest'
import { generatePassword } from '../../src/utils/generatePassword.js'

describe('generatePassword', () => {

  it('should generate a password of exactly 8 characters', () => {
    const password = generatePassword()
    expect(password).toHaveLength(8)
  })

  it('should generate a string', () => {
    const password = generatePassword()
    expect(typeof password).toBe('string')
  })

  it('should not be hashed — no bcrypt prefix', () => {
    const password = generatePassword()
    expect(password).not.toMatch(/^\$2[ab]\$/)
  })

  it('should generate different passwords each time', () => {
    const password1 = generatePassword()
    const password2 = generatePassword()
    expect(password1).not.toBe(password2)
  })

  it('should only contain alphanumeric characters', () => {
    const password = generatePassword()
    expect(password).toMatch(/^[A-Za-z0-9]{8}$/)
  })
})