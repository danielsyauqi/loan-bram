import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

// Date and time utilities
export const dateUtils = {
  // Month conversion functions
  getMonthName: (monthNumber: string | number): string => {
    const monthIndex = typeof monthNumber === 'string' ? parseInt(monthNumber, 10) - 1 : monthNumber - 1;
    if (isNaN(monthIndex) || monthIndex < 0 || monthIndex > 11) {
      return 'Unknown';
    }
    
    return dateUtils.monthNames[monthIndex];
  },
  
  getMonthNumber: (monthName: string): string => {
    const monthIndex = dateUtils.monthNames.findIndex(month => 
      month.toLowerCase() === monthName.toLowerCase()
    );
    
    if (monthIndex === -1) {
      return '00';
    }
    
    // Add 1 and pad with zero if needed
    return (monthIndex + 1).toString().padStart(2, '0');
  },
  
  // Month data
  monthNames: [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ],
  
  months: [
    { value: '01', label: 'January' },
    { value: '02', label: 'February' },
    { value: '03', label: 'March' },
    { value: '04', label: 'April' },
    { value: '05', label: 'May' },
    { value: '06', label: 'June' },
    { value: '07', label: 'July' },
    { value: '08', label: 'August' },
    { value: '09', label: 'September' },
    { value: '10', label: 'October' },
    { value: '11', label: 'November' },
    { value: '12', label: 'December' }
  ],
  
  // Generate years array (current year and n years back)
  getYearsArray: (yearsBack: number = 10) => {
    const currentYear = new Date().getFullYear();
    return Array.from({ length: yearsBack + 1 }, (_, i) => ({
      value: String(currentYear - i),
      label: String(currentYear - i)
    }));
  },
  
  // Format date to a specified format
  formatDate: (date: Date | string, format: string = 'YYYY-MM-DD HH:mm'): string => {
    const d = typeof date === 'string' ? new Date(date) : date;
    
    if (isNaN(d.getTime())) {
      return 'Invalid Date';
    }
    
    const year = d.getFullYear();
    const month = (d.getMonth() + 1).toString().padStart(2, '0');
    const day = d.getDate().toString().padStart(2, '0');
    const monthName = dateUtils.monthNames[d.getMonth()];
    const hours = d.getHours().toString().padStart(2, '0');
    const minutes = d.getMinutes().toString().padStart(2, '0');
    
    let result = format;
    // Replace tokens in order of specificity (longer tokens first)
    result = result.replace(/MMMM/g, monthName);
    result = result.replace(/MM/g, month);
    result = result.replace(/DD/g, day);
    result = result.replace(/YYYY/g, year.toString());
    result = result.replace(/HH/g, hours);
    result = result.replace(/mm/g, minutes);
    
    return result;
  }
};

// Currency formatting
export const formatCurrency = (amount: number | string, currency: string = 'MYR', locale: string = 'ms-MY'): string => {
  return new Intl.NumberFormat(locale, {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(Number(amount));
};

// Array and object utilities
export const arrayUtils = {
  // Parse JSON safely
  safeJsonParse: (jsonString: string | null | undefined, defaultValue: any = []): any => {
    if (!jsonString) return defaultValue;
    try {
      return JSON.parse(jsonString);
    } catch (e) {
      console.error('Error parsing JSON:', e);
      return defaultValue;
    }
  },
  
  // Calculate sum from array of objects with amount property
  sumAmounts: (items: Array<{amount: string | number}> = []): number => {
    return items.reduce((sum, item) => sum + Number(item.amount), 0);
  }
};
