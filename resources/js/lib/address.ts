/**
 * Malaysian Address Library
 * 
 * A TypeScript library for handling Malaysian addresses,
 * including states, cities, postcodes, and validation utilities.
 */

/**
 * Malaysian states and federal territories
 */
export enum MalaysianState {
  JOHOR = 'Johor',
  KEDAH = 'Kedah',
  KELANTAN = 'Kelantan',
  MELAKA = 'Melaka',
  NEGERI_SEMBILAN = 'Negeri Sembilan',
  PAHANG = 'Pahang',
  PENANG = 'Pulau Pinang',
  PERAK = 'Perak',
  PERLIS = 'Perlis',
  SABAH = 'Sabah',
  SARAWAK = 'Sarawak',
  SELANGOR = 'Selangor',
  TERENGGANU = 'Terengganu',
  WP_KUALA_LUMPUR = 'Wilayah Persekutuan Kuala Lumpur',
  WP_LABUAN = 'Wilayah Persekutuan Labuan',
  WP_PUTRAJAYA = 'Wilayah Persekutuan Putrajaya'
}

/**
 * Malaysian postcode ranges by state
 */
export const PostcodeRanges: Record<MalaysianState, { start: number; end: number }[]> = {
  [MalaysianState.JOHOR]: [{ start: 79000, end: 86900 }],
  [MalaysianState.KEDAH]: [{ start: 5000, end: 9810 }],
  [MalaysianState.KELANTAN]: [{ start: 15000, end: 18500 }],
  [MalaysianState.MELAKA]: [{ start: 75000, end: 78000 }],
  [MalaysianState.NEGERI_SEMBILAN]: [{ start: 70000, end: 73509 }],
  [MalaysianState.PAHANG]: [{ start: 25000, end: 28800 }, { start: 39000, end: 39200 }, { start: 49000, end: 49000 }, { start: 69000, end: 69000 }],
  [MalaysianState.PENANG]: [{ start: 10000, end: 14400 }],
  [MalaysianState.PERAK]: [{ start: 30000, end: 36810 }],
  [MalaysianState.PERLIS]: [{ start: 1000, end: 2800 }],
  [MalaysianState.SABAH]: [{ start: 88000, end: 91309 }],
  [MalaysianState.SARAWAK]: [{ start: 93000, end: 98859 }],
  [MalaysianState.SELANGOR]: [{ start: 40000, end: 48300 }, { start: 63000, end: 68100 }],
  [MalaysianState.TERENGGANU]: [{ start: 20000, end: 24300 }],
  [MalaysianState.WP_KUALA_LUMPUR]: [{ start: 50000, end: 60000 }],
  [MalaysianState.WP_LABUAN]: [{ start: 87000, end: 87033 }],
  [MalaysianState.WP_PUTRAJAYA]: [{ start: 62000, end: 62988 }]
};

/**
 * Major cities in each Malaysian state
 */
export const CitiesByState: Record<MalaysianState, string[]> = {
  [MalaysianState.JOHOR]: [
    'Johor Bahru', 'Batu Pahat', 'Muar', 'Kluang', 'Pontian', 'Segamat', 
    'Kota Tinggi', 'Kulai', 'Tangkak', 'Pasir Gudang', 'Skudai', 'Iskandar Puteri'
  ],
  [MalaysianState.KEDAH]: [
    'Alor Setar', 'Sungai Petani', 'Kulim', 'Kubang Pasu', 'Langkawi', 
    'Yan', 'Jitra', 'Pokok Sena', 'Kuala Kedah', 'Baling'
  ],
  [MalaysianState.KELANTAN]: [
    'Kota Bharu', 'Tumpat', 'Pasir Mas', 'Machang', 'Tanah Merah', 
    'Pasir Puteh', 'Kuala Krai', 'Bachok', 'Gua Musang', 'Jeli'
  ],
  [MalaysianState.MELAKA]: [
    'Melaka City', 'Alor Gajah', 'Jasin', 'Masjid Tanah', 'Merlimau',
    'Ayer Keroh', 'Klebang', 'Sungai Udang', 'Umbai', 'Tanjung Kling'
  ],
  [MalaysianState.NEGERI_SEMBILAN]: [
    'Seremban', 'Port Dickson', 'Nilai', 'Bahau', 'Rembau', 
    'Kuala Pilah', 'Tampin', 'Gemas', 'Pedas', 'Senawang'
  ],
  [MalaysianState.PAHANG]: [
    'Kuantan', 'Temerloh', 'Bentong', 'Pekan', 'Jerantut', 
    'Raub', 'Mentakab', 'Cameron Highlands', 'Kuala Lipis', 'Rompin'
  ],
  [MalaysianState.PENANG]: [
    'George Town', 'Butterworth', 'Bukit Mertajam', 'Nibong Tebal', 'Kepala Batas',
    'Tanjung Tokong', 'Balik Pulau', 'Air Itam', 'Batu Ferringhi', 'Seberang Perai'
  ],
  [MalaysianState.PERAK]: [
    'Ipoh', 'Taiping', 'Teluk Intan', 'Sitiawan', 'Batu Gajah', 
    'Kampar', 'Sungai Siput', 'Kuala Kangsar', 'Lumut', 'Tapah', 'Tanjung Malim'
  ],
  [MalaysianState.PERLIS]: [
    'Kangar', 'Arau', 'Kuala Perlis', 'Padang Besar', 'Simpang Empat',
    'Pauh', 'Beseri', 'Chuping', 'Wang Kelian', 'Kaki Bukit'
  ],
  [MalaysianState.SABAH]: [
    'Kota Kinabalu', 'Sandakan', 'Tawau', 'Lahad Datu', 'Keningau', 
    'Semporna', 'Kudat', 'Ranau', 'Beaufort', 'Papar', 'Penampang'
  ],
  [MalaysianState.SARAWAK]: [
    'Kuching', 'Miri', 'Sibu', 'Bintulu', 'Limbang', 
    'Samarahan', 'Sarikei', 'Sri Aman', 'Kapit', 'Mukah', 'Serian'
  ],
  [MalaysianState.SELANGOR]: [
    'Shah Alam', 'Petaling Jaya', 'Klang', 'Subang Jaya', 'Ampang', 
    'Kajang', 'Selayang', 'Rawang', 'Sepang', 'Banting', 'Cyberjaya', 'Puchong'
  ],
  [MalaysianState.TERENGGANU]: [
    'Kuala Terengganu', 'Kemaman', 'Dungun', 'Marang', 'Besut', 
    'Hulu Terengganu', 'Setiu', 'Kuala Nerus', 'Paka', 'Kerteh'
  ],
  [MalaysianState.WP_KUALA_LUMPUR]: [
    'Kuala Lumpur', 'Wangsa Maju', 'Cheras', 'Bukit Bintang', 'Titiwangsa',
    'Setapak', 'Kepong', 'Sentul', 'Batu', 'Lembah Pantai', 'Bandar Tun Razak'
  ],
  [MalaysianState.WP_LABUAN]: [
    'Labuan', 'Rancha-Rancha', 'Layang-Layangan', 'Tanjung Aru', 'Sungai Lada',
    'Sungai Bedaun', 'Sungai Keling', 'Pohon Batu', 'Sungai Miri', 'Lubok Temiang'
  ],
  [MalaysianState.WP_PUTRAJAYA]: [
    'Putrajaya', 'Precinct 1', 'Precinct 2', 'Precinct 3', 'Precinct 4',
    'Precinct 5', 'Precinct 6', 'Precinct 7', 'Precinct 8', 'Precinct 9', 'Precinct 10'
  ]
};

/**
 * Malaysian address interface
 */
export interface MalaysianAddress {
  addressLine1: string;
  addressLine2?: string;
  city: string;
  state: MalaysianState;
  postcode: string;
  country: 'Malaysia';
}

/**
 * Validate a Malaysian postcode
 * 
 * @param postcode - Postcode to validate
 * @param state - Optional state to validate against
 * @returns Object with validation result and error message if invalid
 */
export function validatePostcode(
  postcode: string, 
  state?: MalaysianState
): { isValid: boolean; message?: string } {
  // Postcode should be 5 digits
  if (!/^\d{5}$/.test(postcode)) {
    return {
      isValid: false,
      message: 'Postcode must be 5 digits'
    };
  }

  const postcodeNum = parseInt(postcode, 10);
  
  // If state is provided, check if postcode belongs to that state
  if (state) {
    const stateRanges = PostcodeRanges[state];
    const isInState = stateRanges.some(range => 
      postcodeNum >= range.start && postcodeNum <= range.end
    );
    
    if (!isInState) {
      return {
        isValid: false,
        message: `Postcode ${postcode} is not in ${state}`
      };
    }
  } else {
    // Check if postcode belongs to any state
    const belongsToAnyState = Object.values(PostcodeRanges).some(stateRanges =>
      stateRanges.some(range => postcodeNum >= range.start && postcodeNum <= range.end)
    );
    
    if (!belongsToAnyState) {
      return {
        isValid: false,
        message: `Postcode ${postcode} is not a valid Malaysian postcode`
      };
    }
  }
  
  return { isValid: true };
}

/**
 * Validate if a city exists in a given state
 * 
 * @param city - City name to validate
 * @param state - State to check against
 * @returns Object with validation result and error message if invalid
 */
export function validateCity(
  city: string, 
  state: MalaysianState
): { isValid: boolean; message?: string } {
  const cities = CitiesByState[state];
  
  // Case-insensitive search for city
  const cityExists = cities.some(c => 
    c.toLowerCase() === city.toLowerCase()
  );
  
  if (!cityExists) {
    return {
      isValid: false,
      message: `${city} is not a recognized city in ${state}`
    };
  }
  
  return { isValid: true };
}

/**
 * Find the state based on postcode
 * 
 * @param postcode - Postcode to look up
 * @returns State name or undefined if not found
 */
export function findStateByPostcode(postcode: string): MalaysianState | undefined {
  if (!/^\d{5}$/.test(postcode)) {
    return undefined;
  }
  
  const postcodeNum = parseInt(postcode, 10);
  
  for (const [stateName, ranges] of Object.entries(PostcodeRanges)) {
    const isInState = ranges.some(range => 
      postcodeNum >= range.start && postcodeNum <= range.end
    );
    
    if (isInState) {
      return stateName as MalaysianState;
    }
  }
  
  return undefined;
}

/**
 * Get cities for a given state
 * 
 * @param state - State to get cities for
 * @returns Array of city names in the state
 */
export function getCitiesByState(state: MalaysianState): string[] {
  return CitiesByState[state];
}

/**
 * Get all states in Malaysia
 * 
 * @returns Array of all Malaysian states
 */
export function getAllStates(): MalaysianState[] {
  return Object.values(MalaysianState);
}

/**
 * Format a Malaysian address to standard format
 * 
 * @param address - Address to format
 * @returns Formatted address string
 */
export function formatAddress(address: MalaysianAddress): string {
  const lines = [
    address.addressLine1,
    address.addressLine2,
    `${address.postcode} ${address.city}`,
    address.state,
    'Malaysia'
  ].filter(Boolean); // Remove undefined/empty lines
  
  return lines.join('\n');
}

/**
 * Validate a complete Malaysian address
 * 
 * @param address - Address to validate
 * @returns Object with validation result and error messages if any
 */
export function validateAddress(
  address: MalaysianAddress
): { isValid: boolean; errors: Record<string, string> } {
  const errors: Record<string, string> = {};
  
  // Validate required fields
  if (!address.addressLine1?.trim()) {
    errors.addressLine1 = 'Address line 1 is required';
  }
  
  if (!address.city?.trim()) {
    errors.city = 'City is required';
  } else {
    // Validate city is in the state
    const cityValidation = validateCity(address.city, address.state);
    if (!cityValidation.isValid) {
      errors.city = cityValidation.message || 'Invalid city';
    }
  }
  
  if (!address.state) {
    errors.state = 'State is required';
  }
  
  if (!address.postcode?.trim()) {
    errors.postcode = 'Postcode is required';
  } else {
    // Validate postcode
    const postcodeValidation = validatePostcode(address.postcode, address.state);
    if (!postcodeValidation.isValid) {
      errors.postcode = postcodeValidation.message || 'Invalid postcode';
    }
  }
  
  return {
    isValid: Object.keys(errors).length === 0,
    errors
  };
}

/**
 * Get a list of nearby cities within a state
 * 
 * @param city - Reference city
 * @param state - State to look in
 * @param limit - Maximum number of cities to return
 * @returns Array of cities in the same state
 */
export function getNearbyCities(city: string, state: MalaysianState, limit = 5): string[] {
  const cities = CitiesByState[state];
  const cityIndex = cities.findIndex(c => c.toLowerCase() === city.toLowerCase());
  
  if (cityIndex === -1) {
    return [];
  }
  
  // Get cities before and after the target city
  const result: string[] = [];
  const halfLimit = Math.floor(limit / 2);
  
  for (let i = 1; i <= halfLimit; i++) {
    const beforeIndex = cityIndex - i;
    if (beforeIndex >= 0) {
      result.unshift(cities[beforeIndex]);
    }
    
    const afterIndex = cityIndex + i;
    if (afterIndex < cities.length) {
      result.push(cities[afterIndex]);
    }
  }
  
  // Fill up to the limit if we haven't reached it
  if (result.length < limit) {
    for (let i = halfLimit + 1; result.length < limit; i++) {
      const beforeIndex = cityIndex - i;
      if (beforeIndex >= 0) {
        result.unshift(cities[beforeIndex]);
        continue;
      }
      
      const afterIndex = cityIndex + i;
      if (afterIndex < cities.length) {
        result.push(cities[afterIndex]);
        continue;
      }
      
      break; // No more cities to add
    }
  }
  
  return result;
}

/**
 * Get postcode range for a state
 * 
 * @param state - State to get postcode range for
 * @returns Array of postcode ranges for the state
 */
export function getPostcodeRanges(state: MalaysianState): { start: number; end: number }[] {
  return PostcodeRanges[state];
}

/**
 * Check if a state is East Malaysian (Borneo)
 * 
 * @param state - State to check
 * @returns True if state is in East Malaysia
 */
export function isEastMalaysian(state: MalaysianState): boolean {
  return state === MalaysianState.SABAH || state === MalaysianState.SARAWAK || state === MalaysianState.WP_LABUAN;
}

/**
 * Get states with dropdown-friendly format
 * 
 * @returns Array of state objects with label and value properties
 */
export function getStatesForDropdown(): Array<{ label: string; value: MalaysianState }> {
  return Object.values(MalaysianState).map(state => ({
    label: state,
    value: state
  }));
}

/**
 * Get cities for a state with dropdown-friendly format
 * 
 * @param state - State to get cities for
 * @returns Array of city objects with label and value properties
 */
export function getCitiesForDropdown(state: MalaysianState): Array<{ label: string; value: string }> {
  return CitiesByState[state].map(city => ({
    label: city,
    value: city
  }));
}

export function stateSelection() {
  return Object.values(MalaysianState);
}

export function citySelection(state: MalaysianState) {
  return CitiesByState[state];
}

