'use client'

import { useState } from 'react'
import { submitAction } from './actions'

const SHELLS = [
  {
    id: 'nautilus',
    name: 'Chambered Nautilus',
    icon: '🐚',
    desc: 'The masterpiece of nature’s geometry.',
    image: '/pictures/shell1.jpg',
    facts: ['Found in the deep slopes of Indo-Pacific reefs', 'Can live up to 20 years', 'They use jet propulsion to swim']
  },
  {
    id: 'conch',
    name: 'Queen Conch',
    icon: '🐚',
    desc: 'Famous for its pink interior and size.',
    image: '/pictures/shell2.jpg',
    facts: ['Commonly found in the Caribbean', 'Produces rare pink pearls', 'Used as a ceremonial trumpet in many cultures']
  },
  {
    id: 'scallop',
    name: 'Golden Scallop',
    icon: '🐚',
    desc: 'The iconic shell of the sea.',
    image: '/pictures/shell3.jpg',
    facts: ['Can swim by clapping their shells together', 'Have dozens of tiny blue eyes along the edge', 'The symbol of Venus in classical art']
  }
]

export default function Home() {
  const [result, setResult] = useState<string>('')
  const [comment, setComment] = useState('')
  const [loading, setLoading] = useState(false)
  const [selectedShell, setSelectedShell] = useState<typeof SHELLS[0] | null>(null)

  const handleCommentSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    try {
      const response = await submitAction({ message: comment });
      setResult(response);
      setComment('');
    } catch (error) {
      setResult(`Error: ${error instanceof Error ? error.message : 'Submission failed'}`);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div style={{ minHeight: '100vh', backgroundColor: '#fff7ed', fontFamily: 'serif', color: '#431407' }}>
      <header style={{ backgroundColor: '#ea580c', color: 'white', padding: '1.5rem 2rem', boxShadow: '0 4px 6px rgba(0,0,0,0.1)' }}>
        <div style={{ maxWidth: '1200px', margin: '0 auto', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <h1 style={{ margin: 0, fontSize: '28px', fontWeight: '900' }}>SeaShell Enthusiasts 🐚</h1>
          <nav style={{ display: 'flex', gap: '1.5rem', fontWeight: '600' }}>
            <span>Collection</span>
            <span>The Team</span>
          </nav>
        </div>
      </header>

      <main style={{ maxWidth: '1200px', margin: '0 auto', padding: '3rem 2rem' }}>
        <div style={{ textAlign: 'center', marginBottom: '3rem' }}>
          <h2 style={{ fontSize: '42px', fontWeight: '800', color: '#9a3412' }}>The Shell Collectors Circle</h2>
          <p style={{ fontSize: '18px', color: '#7c2d12' }}>A project by Romaric, Photos by Abdul, Managed by Marcus.</p>
        </div>
       
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '2rem', marginBottom: '4rem' }}>
          {SHELLS.map((shell) => (
            <div
              key={shell.id}
              onClick={() => setSelectedShell(shell)}
              style={{ backgroundColor: 'white', borderRadius: '16px', overflow: 'hidden', boxShadow: '0 4px 15px rgba(0,0,0,0.05)', cursor: 'pointer' }}
            >
              <div style={{ height: '200px', backgroundColor: '#fdba74', display: 'flex', justifyContent: 'center', alignItems: 'center', fontSize: '64px' }}>{shell.icon}</div>
              <div style={{ padding: '1.5rem' }}>
                <h3 style={{ margin: '0 0 0.5rem 0', color: '#ea580c' }}>{shell.name}</h3>
                <p style={{ margin: 0, color: '#7c2d12', fontSize: '14px' }}>{shell.desc}</p>
              </div>
            </div>
          ))}
        </div>

        {selectedShell && (
          <div style={{ position: 'fixed', top: 0, left: 0, width: '100%', height: '100%', backgroundColor: 'rgba(67, 20, 7, 0.8)', display: 'flex', justifyContent: 'center', alignItems: 'center', zIndex: 1000 }} onClick={() => setSelectedShell(null)}>
            <div style={{ backgroundColor: 'white', borderRadius: '24px', padding: '2rem', maxWidth: '500px', width: '90%', position: 'relative' }} onClick={e => e.stopPropagation()}>
              <img src={selectedShell.image} alt={selectedShell.name} style={{ width: '100%', borderRadius: '12px', marginBottom: '1.5rem' }} />
              <h2 style={{ color: '#9a3412' }}>{selectedShell.name}</h2>
              <ul style={{ color: '#7c2d12' }}>
                {selectedShell.facts.map((fact, i) => <li key={i}>{fact}</li>)}
              </ul>
            </div>
          </div>
        )}

        <div style={{ maxWidth: '700px', margin: '0 auto', backgroundColor: '#fed7aa', borderRadius: '24px', padding: '2.5rem' }}>
          <h3 style={{ fontSize: '24px', fontWeight: '700', marginBottom: '1.5rem', color: '#9a3412' }}>Join the Discussion</h3>
          <form onSubmit={handleCommentSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
            <textarea
              value={comment}
              onChange={(e) => setComment(e.target.value)}
              placeholder="Leave a message for the collectors..."
              style={{ width: '100%', minHeight: '100px', padding: '1rem', borderRadius: '12px', border: 'none', outline: 'none' }}
            />
            <button disabled={loading} style={{ backgroundColor: '#ea580c', color: 'white', border: 'none', padding: '1rem', borderRadius: '12px', fontWeight: 'bold', cursor: 'pointer' }}>
              {loading ? 'Sending...' : 'Post Comment'}
            </button>
          </form>
          {result && <div style={{ marginTop: '1rem', color: '#9a3412', fontWeight: 'bold' }}>{result}</div>}
        </div>
      </main>

      <footer style={{ textAlign: 'center', padding: '3rem', color: '#7c2d12', fontSize: '14px' }}>
        <p>Managed by <strong>Marcus</strong>. All shell photos provided by <strong>Abdul</strong>.</p>
        <p>© 2026 SeaShell Circle - System Admin: Romaric</p>
        <p> Powered by React! </p>
      </footer>
    </div>
  )
}
