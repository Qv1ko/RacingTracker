import Link from 'next/link';
import React from 'react';

const Header: React.FC = () => {
  return (
    <header className="bg-blue-600 text-white p-4">
      <div className="container mx-auto flex justify-between items-center">
        <h1 className="text-xl font-bold">
          <Link href="/">
            Mi Aplicación
          </Link>
        </h1>
        <nav>
          <ul className="flex space-x-4">
            <li>
              <Link href="/">Home</Link>
            </li>
            <li>
              <Link href="/drivers">Drivers</Link>
            </li>
            <li>
              <Link href="/teams">Teams</Link>
            </li>
          </ul>
        </nav>
      </div>
    </header>
  );
};

export default Header;
