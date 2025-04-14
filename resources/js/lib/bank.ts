/**
 * Malaysian Bank Account Library
 * 
 * A TypeScript library for handling Malaysian bank account operations,
 * including validation, formatting, and utilities.
 */

/**
 * List of Malaysian banks with their codes and account number patterns
 */
export const MalaysianBanks = {
  MAYBANK: {
    code: 'MBB',
    name: 'Maybank',
    accountLength: [12],
    swift: 'MBBEMYKL',
    pattern: /^\d{12}$/
  },
  CIMB: {
    code: 'CIMB',
    name: 'CIMB Bank',
    accountLength: [10, 14],
    swift: 'CIBBMYKL',
    pattern: /^\d{10}$|^\d{14}$/
  },
  PUBLIC_BANK: {
    code: 'PBB',
    name: 'Public Bank',
    accountLength: [10],
    swift: 'PBBEMYKL',
    pattern: /^\d{10}$/
  },
  RHB: {
    code: 'RHB',
    name: 'RHB Bank',
    accountLength: [14],
    swift: 'RHBBMYKL',
    pattern: /^\d{14}$/
  },
  HONG_LEONG: {
    code: 'HLBB',
    name: 'Hong Leong Bank',
    accountLength: [12],
    swift: 'HLBBMYKL', 
    pattern: /^\d{12}$/
  },
  AMBANK: {
    code: 'AMBB',
    name: 'AmBank',
    accountLength: [12],
    swift: 'ARBKMYKL',
    pattern: /^\d{12}$/
  },
  BANK_ISLAM: {
    code: 'BIMB',
    name: 'Bank Islam Malaysia',
    accountLength: [14],
    swift: 'BIMBMYKL',
    pattern: /^\d{14}$/
  },
  BANK_RAKYAT: {
    code: 'BKRM',
    name: 'Bank Rakyat',
    accountLength: [12],
    swift: 'BKRMMYKL',
    pattern: /^\d{12}$/
  },
  ALLIANCE: {
    code: 'ALB',
    name: 'Alliance Bank',
    accountLength: [12],
    swift: 'MFBBMYKL',
    pattern: /^\d{12}$/
  },
  BANK_MUAMALAT: {
    code: 'BMMB',
    name: 'Bank Muamalat Malaysia',
    accountLength: [14],
    swift: 'BMMBMYKL',
    pattern: /^\d{14}$/
  },
  HSBC: {
    code: 'HSBC',
    name: 'HSBC Bank Malaysia',
    accountLength: [12],
    swift: 'HBMBMYKL',
    pattern: /^\d{12}$/
  },
  OCBC: {
    code: 'OCBC',
    name: 'OCBC Bank',
    accountLength: [10],
    swift: 'OCBCMYKL',
    pattern: /^\d{10}$/
  },
  STANDARD_CHARTERED: {
    code: 'SCB',
    name: 'Standard Chartered',
    accountLength: [10],
    swift: 'SCBLMYKL',
    pattern: /^\d{10}$/
  },
  UOB: {
    code: 'UOB',
    name: 'United Overseas Bank',
    accountLength: [10],
    swift: 'UOVBMYKL',
    pattern: /^\d{10}$/
  },
  AFFIN: {
    code: 'AFB',
    name: 'Affin Bank',
    accountLength: [12],
    swift: 'PHBMMYKL',
    pattern: /^\d{12}$/
  }
};

export type BankCode = keyof typeof MalaysianBanks;

/**
 * Bank account interface
 */
export interface BankAccount {
  accountNumber: string;
  bankCode: BankCode | string;
  accountHolderName: string;
  accountType?: 'Savings' | 'Current' | 'Fixed Deposit';
  branchCode?: string;
}

/**
 * Format a bank account number with proper spacing based on bank
 * 
 * @param accountNumber - Raw account number
 * @param bankCode - Bank identifier
 * @returns Formatted account number
 */
export function formatBankAccount(accountNumber: string, bankCode: BankCode | string): string {
  // Remove any non-digit characters
  const cleanNumber = accountNumber.replace(/\D/g, '');
  
  // Format according to bank's standard
  switch (bankCode) {
    case 'MAYBANK':
      // Maybank format: XXXX-XXXX-XXXX
      return cleanNumber.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
    
    case 'CIMB':
      // CIMB format depends on length
      if (cleanNumber.length === 10) {
        return cleanNumber.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
      } else if (cleanNumber.length === 14) {
        return cleanNumber.replace(/(\d{4})(\d{4})(\d{4})(\d{2})/, '$1-$2-$3-$4');
      }
      return cleanNumber;
    
    case 'PUBLIC_BANK':
      // Public Bank format: XXX-XXX-XXXX
      return cleanNumber.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
    
    case 'RHB':
      // RHB format: XXXX-XXXX-XXXX-XX
      return cleanNumber.replace(/(\d{4})(\d{4})(\d{4})(\d{2})/, '$1-$2-$3-$4');
    
    default:
      // For other banks or custom formats:
      // If it's a 10-digit account
      if (cleanNumber.length === 10) {
        return cleanNumber.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
      }
      // If it's a 12-digit account
      else if (cleanNumber.length === 12) {
        return cleanNumber.replace(/(\d{4})(\d{4})(\d{4})/, '$1-$2-$3');
      }
      // If it's a 14-digit account
      else if (cleanNumber.length === 14) {
        return cleanNumber.replace(/(\d{4})(\d{4})(\d{4})(\d{2})/, '$1-$2-$3-$4');
      }
      
      // Default: Just return with uniform spacing
      return cleanNumber.match(/.{1,4}/g)?.join('-') || cleanNumber;
  }
}

/**
 * Validate a Malaysian bank account number
 * 
 * @param accountNumber - Account number to validate
 * @param bankCode - Bank identifier (optional)
 * @returns Object with validation result and error message if any
 */
export function validateBankAccount(
  accountNumber: string, 
  bankCode?: BankCode | string
): { isValid: boolean; message?: string } {
  // Remove any non-digit characters
  const cleanNumber = accountNumber.replace(/\D/g, '');
  
  // Basic validation - check if account number contains only digits
  if (!/^\d+$/.test(cleanNumber)) {
    return { 
      isValid: false, 
      message: 'Account number should contain only digits' 
    };
  }
  
  // If bank code is provided, validate against specific bank pattern
  if (bankCode && bankCode in MalaysianBanks) {
    const bank = MalaysianBanks[bankCode as BankCode];
    
    // Check if account length matches expected length for this bank
    if (!bank.accountLength.includes(cleanNumber.length)) {
      return { 
        isValid: false, 
        message: `${bank.name} account numbers should be ${bank.accountLength.join(' or ')} digits long` 
      };
    }
    
    // Test against bank-specific pattern if available
    if (bank.pattern && !bank.pattern.test(cleanNumber)) {
      return { 
        isValid: false, 
        message: `Invalid account number format for ${bank.name}` 
      };
    }
    
    return { isValid: true };
  }
  
  // Generic validation based on common account lengths in Malaysia
  const validLengths = [10, 12, 14];
  if (!validLengths.includes(cleanNumber.length)) {
    return { 
      isValid: false, 
      message: 'Malaysian bank account numbers are typically 10, 12, or 14 digits long' 
    };
  }
  
  return { isValid: true };
}

/**
 * Get bank information by bank code
 * 
 * @param bankCode - Bank identifier
 * @returns Bank information or undefined if not found
 */
export function getBankInfo(bankCode: BankCode | string) {
  if (bankCode in MalaysianBanks) {
    return MalaysianBanks[bankCode as BankCode];
  }
  return undefined;
}

/**
 * Get all Malaysian banks as an array
 * 
 * @returns Array of bank information objects
 */
export function getAllBanks() {
  return Object.values(MalaysianBanks);
}

/**
 * Mask a bank account number for display/security
 * 
 * @param accountNumber - Full account number
 * @returns Masked account number (e.g., XXXX-XXXX-1234)
 */
export function maskAccountNumber(accountNumber: string): string {
  const cleanNumber = accountNumber.replace(/\D/g, '');
  
  if (cleanNumber.length <= 4) {
    return cleanNumber;
  }
  
  // Show only last 4 digits
  const lastFour = cleanNumber.slice(-4);
  const maskedPart = 'X'.repeat(cleanNumber.length - 4);
  
  // Format with dashes every 4 characters
  const combined = maskedPart + lastFour;
  return combined.match(/.{1,4}/g)?.join('-') || combined;
}

/**
 * Generate IBAN for Malaysian accounts
 * (Note: Malaysia doesn't use IBAN in practice, but this is for international transfers)
 * 
 * @param accountNumber - Account number
 * @param bankCode - Bank code
 * @returns Pseudo-IBAN for international reference
 */
export function generatePseudoIBAN(accountNumber: string, bankCode: BankCode): string {
  if (!(bankCode in MalaysianBanks)) {
    throw new Error(`Unknown bank code: ${bankCode}`);
  }
  
  const cleanNumber = accountNumber.replace(/\D/g, '');
  const bank = MalaysianBanks[bankCode];
  
  // Format: MY + 2 check digits + bank code (padded) + account number (padded)
  // This is a simplified version and not an official IBAN
  const bankCodePadded = bank.code.padEnd(4, '0');
  const accountPadded = cleanNumber.padStart(16, '0');
  
  // Note: Real IBAN would require proper check digit calculation
  // This is just for demonstration
  return `MY00${bankCodePadded}${accountPadded}`;
}

/**
 * Find potential bank by account number pattern
 * 
 * @param accountNumber - Account number to check
 * @returns Array of potential matching banks
 */
export function identifyBankByAccountNumber(accountNumber: string): Array<typeof MalaysianBanks[BankCode]> {
  const cleanNumber = accountNumber.replace(/\D/g, '');
  const matches = [];
  
  for (const [code, bank] of Object.entries(MalaysianBanks)) {
    if (bank.accountLength.includes(cleanNumber.length)) {
      if (bank.pattern && bank.pattern.test(cleanNumber)) {
        matches.push(bank);
      }
    }
  }
  
  
  return matches;
}

/**
 * Get bank selection options
 * 
 * @returns Array of bank information objects
 */
export function bankSelection() {
  return Object.values(MalaysianBanks);
}

export function bankAccountNumberValidation(accountNumber: string , bankCode: BankCode | string) {
  const cleanNumber = accountNumber.replace(/\D/g, '');
  return validateBankAccount(cleanNumber, bankCode);
}
