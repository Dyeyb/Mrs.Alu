-- ══════════════════════════════════════════════════════
--  CAL ELITE  ·  Chat Messages Table
--  Run this once in Supabase SQL Editor
-- ══════════════════════════════════════════════════════

create table if not exists public.chat_messages (
    id           bigserial primary key,
    order_ref    text        not null,          -- e.g. "BK-SEED02"
    sender       text        not null check (sender in ('customer','courier')),
    message      text        not null,
    sent_at      timestamptz not null default now()
);

-- Index for fast lookups by order ref
create index if not exists idx_chat_order_ref on public.chat_messages (order_ref, sent_at);

-- Enable Row Level Security (open policy for now — tighten later)
alter table public.chat_messages enable row level security;

create policy "Allow all reads"
    on public.chat_messages for select using (true);

create policy "Allow all inserts"
    on public.chat_messages for insert with check (true);

-- Enable Realtime for this table
alter publication supabase_realtime add table public.chat_messages;
