import React from 'react';
import Image from 'next/image';

interface Props {
    placeholder: string;
    searchTerm: string;
    setSearchTerm: (term: string) => void;
}

const FieldSearchBar: React.FC<Props> = ({ placeholder, searchTerm, setSearchTerm }) => {
    return (
        <div className="relative w-full sm:w-64">
            <div className="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <Image
                    className="dark:invert"
                    src="/search.svg"
                    alt="Search"
                    width={16}
                    height={16}
                    priority
                />
            </div>
            <input
                type="text"
                className="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100 text-sm rounded-sm focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                placeholder={placeholder}
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
            />
        </div>
    );
};

export default FieldSearchBar;
