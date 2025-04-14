export type ApplicationStatus = 
  | 'New'
  | 'Pending'
  | 'Approved'
  | 'Disbursed'
  | 'Rejected'
  | 'Ready to Submit'
  | 'Processing'
  | 'Pending@Agency'
  | 'Pending@Bank'
  | 'Delete Request'
  | 'Verified'
  | 'Not Verified'
  | 'Active'
  | 'Inactive';

// Map of status to color scheme classes
export const statusColorMap: Record<ApplicationStatus, string> = {
  'New': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100',
  'Pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100',
  'Approved': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
  'Disbursed': 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100',
  'Rejected': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100',
  'Ready to Submit': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-100',
  'Processing': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100',
  'Pending@Agency': 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-100',
  'Pending@Bank': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-100',
  'Delete Request': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100',
  'Verified': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
  'Not Verified': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100',
  'Active': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
  'Inactive': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100',
};

// Helper function to get color classes for a status
export function getStatusColor(status: string): string {
  const normalizedStatus = status?.trim() || '';
  
  // Try to match case-insensitive
  const matchedStatus = Object.keys(statusColorMap).find(
    key => key.toLowerCase() === normalizedStatus.toLowerCase()
  ) as ApplicationStatus | undefined;
  
  return matchedStatus 
    ? statusColorMap[matchedStatus] 
    : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100';
} 

export type userRole =
  | 'admin'
  | 'customer'
  | 'agent'
  | 'sub agent'


export const roleColorMap: Record<userRole, string> = {
  'admin': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100',
  'customer': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
  'agent': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100',
  'sub agent': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-100',
};

export function getRoleColor(role: string): string {
  const normalizedRole = role?.trim() || '';
  
  // Try to match case-insensitive
  const matchedRole = Object.keys(roleColorMap).find(
    key => key.toLowerCase() === normalizedRole.toLowerCase()
  ) as userRole | undefined;
  
  return matchedRole 
    ? roleColorMap[matchedRole] 
    : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100';
}