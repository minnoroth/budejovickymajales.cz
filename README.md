# Budějovický Majáles

Official website and marketing system for Budějovický Majáles festival.

## Tech Stack

- **Frontend**: Next.js 15, React 19, TypeScript, Material-UI (MUI)
- **Backend**: Strapi 5 (Headless CMS)
- **Database**: SQLite (development), PostgreSQL (production - planned)
- **Package Manager**: Yarn
- **API**: REST

## Project Structure

```
budejovickymajales.cz/
├── frontend/          # Next.js application
│   ├── src/
│   │   ├── app/      # App Router pages
│   │   ├── components/ # React components
│   │   └── theme/    # MUI theme configuration
│   └── package.json
├── backend/          # Strapi CMS
│   ├── src/
│   │   └── api/      # API endpoints
│   └── package.json
└── package.json      # Root workspace configuration
```

## Prerequisites

- Node.js >= 20.0.0
- Yarn >= 1.22.0

## Getting Started

### 1. Install Dependencies

```bash
# Install all dependencies for both frontend and backend
yarn install
```

### 2. Setup Environment Variables

#### Frontend

Create `frontend/.env.local` file:

```env
NEXT_PUBLIC_STRAPI_API_URL=http://localhost:1337
NEXT_PUBLIC_SITE_URL=http://localhost:3000
```

#### Backend

The backend will generate `.env` file automatically on first run. You can customize it as needed.

### 3. Run Development Servers

#### Option A: Run Both (Recommended)

```bash
# Run both frontend and backend concurrently
yarn dev
```

#### Option B: Run Separately

```bash
# Terminal 1 - Frontend (http://localhost:3000)
yarn dev:frontend

# Terminal 2 - Backend (http://localhost:1337)
yarn dev:backend
```

### 4. Access the Applications

- **Frontend**: http://localhost:3000
- **Backend Admin**: http://localhost:1337/admin (first run will prompt to create admin user)
- **Backend API**: http://localhost:1337/api

## Available Scripts

### Root Level

- `yarn dev` - Run both frontend and backend
- `yarn build` - Build both applications
- `yarn dev:frontend` - Run frontend only
- `yarn dev:backend` - Run backend only
- `yarn start:frontend` - Start frontend production server
- `yarn start:backend` - Start backend production server
- `yarn lint:frontend` - Lint frontend code

### Frontend

```bash
cd frontend
yarn dev          # Start development server
yarn build        # Build for production
yarn start        # Start production server
yarn lint         # Run ESLint
```

### Backend

```bash
cd backend
yarn develop      # Start development server with admin panel
yarn start        # Start production server
yarn build        # Build admin panel
yarn strapi       # Access Strapi CLI
```

## Development Guidelines

### Code Quality

- All code must be in **English** (comments, identifiers, UI texts)
- Use **TypeScript** with strict typing - avoid `any` type
- Follow existing code style and conventions
- Write clean, readable, and maintainable code

### React Components

- Use functional components with hooks
- Keep components small and focused (cognitive complexity ≤ 3)
- Avoid unnecessary re-renders
- Use `react-hook-form` for all forms
- Minimize `useEffect` usage

### Git Workflow

- Write clear, descriptive commit messages
- Keep commits focused and atomic
- Follow conventional commits format

## Project Goals

### Phase 1: MVP (Current)

- ✅ Setup Next.js with TypeScript and MUI
- ✅ Setup Strapi CMS
- ✅ Create basic project structure
- ✅ Create simple homepage

### Phase 2: Core Features (Planned)

- Content management through Strapi
- Event listing and details
- News and announcements
- Photo gallery
- Contact forms

### Phase 3: Marketing System (Planned)

- Newsletter integration
- Social media integration
- Analytics and tracking
- SEO optimization

## Resources

- [Next.js Documentation](https://nextjs.org/docs)
- [MUI Documentation](https://mui.com/)
- [Strapi Documentation](https://docs.strapi.io/)
- [TypeScript Documentation](https://www.typescriptlang.org/docs/)

## License

Private project for Budějovický Majáles organization.

## Support

For questions or issues, please contact the development team.

