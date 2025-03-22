import Image from 'next/image';
import Link from 'next/link';
import React from 'react';

const Header: React.FC = () => {
  return (
    <header className="sticky top-0 bg-black text-white p-4 drop-shadow-lg">
      <div className="container mx-auto flex justify-between items-center">
        <Link href="/">
          <Image src="/racingtracker.svg" alt="RacingTracker logo" width={96} height={48} priority />
        </Link>
        <nav>
          <ul className="flex space-x-4 font-semibold">
            <li>
              <Link href="/">HOME</Link>
            </li>
            <li>
              <Link href="/seasons">SEASONS</Link>
            </li>
            <li>
              <Link href="/races">RACES</Link>
            </li>
            <li>
              <Link href="/drivers">DRIVERS</Link>
            </li>
            <li>
              <Link href="/teams">TEAMS</Link>
            </li>
          </ul>
        </nav>
      </div>
    </header>
  );
};

export default Header;
