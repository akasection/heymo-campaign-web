# **(Rashemi) Senior Full Stack Developer \-- Take-Home Assignment**

## **Overview**

Build a small-scale proof of concept of an **intent-led campaign system**.

This is for a **US direct-to-consumer blood testing** business \-- lab panels sold directly to consumers rather than through a doctor's office.

Most marketing funnels push every prospect through one generic campaign. We don't want that. Someone who arrived worried about persistent fatigue and someone who arrived wanting to optimise athletic performance should not receive the same follow-up. Every touchpoint should read like the next sentence of the last one.

Your job is to prove that loop works end to end, at small scale:

**A visitor lands on an angle-specific page → they tell us who they are and what they want → the system sends them a campaign written for that specific person.**

The landing pages are the easy part and are deliberately scoped down. **The personalisation is the assignment** \-- turning page intent plus visitor profile plus brand voice into email that reads as though someone wrote it for them.

You are building a demonstration, not a marketing platform. A narrow slice that visibly works beats a broad slice that only half-connects.

**Time expectation: around 20 hours.** 

---

## **Tech Stack (required)**

| Layer | Technology |
| :---- | :---- |
| Framework | Laravel 9 |
| Language | PHP 8.1 |
| Database | PostgreSQL |
| Frontend | Vue 3 |
| Styling | Tailwind CSS |

Please stick to this stack \-- part of the evaluation is seeing your work within these constraints.

---

## **LLM API Access**

**We will provide you with API keys** \-- you will not need to pay for anything or use your own account. Keys will be sent separately once you begin.

We use **open-source models** served through **Together AI** or **Fireworks AI** (for example, Kimi 2.7). Both providers expose an OpenAI-compatible chat completions API, so most standard client libraries will work with a changed base URL.

The available models should be **configurable rather than hardcoded** \-- we swap models and providers regularly.

---

## **Requirements**

### **1\. Brand Management & Constraints**

The system supports **multiple brands**, and every angle and campaign belongs to one. Each brand defines:

* **Visual identity** \-- colors, fonts, logo.  
* **Copy and tone preferences** \-- voice (e.g. clinical and reassuring vs. warm and conversational), reading level, sign-off style, phrases to always use, phrases to never use, and any compliance language that must appear.

Requirements:

* Two visitors in identical situations but on different brands must receive **recognisably different emails** \-- different look *and* different voice. If only the logo changes, the constraints are not doing their job.  
* Constraints must be **enforced, not merely suggested to the model**. Explain your approach.  
* Admins can manage brands and their preferences through the UI.

### **2\. Angle Definition (authenticated)**

An **angle** is the unit campaigns hang off \-- a coherent theory of why a particular person should care right now. It is not a headline.

Admins can create and manage angles. Each angle captures:

| Field | What it answers |
| :---- | :---- |
| Audience | Who is this for, and what separates them from the general market? |
| Trigger moment | What happened just before they became receptive? |
| Primary job | What progress are they trying to make? |
| Tension | What is frustrating, worrying, confusing, or unresolved? |
| Desired outcome | What does "better" look and feel like to them? |
| Single promise | What can we credibly help them achieve? |
| Proof | Which approved facts, credentials, or mechanisms support it? |
| Objection | What would stop them believing or acting? |
| Offer | What makes acting now easier? |
| Tone | Reassuring, authoritative, warm, matter-of-fact, etc. |
| Next step | The smallest clear action they should take. |

Each landing page maps to an angle, and the angle feeds campaign generation. Not every field has to drive logic \-- deciding which ones reach the model and which stay as human documentation is part of the design. Say what you chose and why.

### **3\. Angle Landing Pages (static)**

At least **three landing pages, each representing a different angle** \-- for example fatigue/low energy, athletic performance, and family history risk.

**Build these as ordinary static pages** \-- hand-written Blade templates and Vue components, one per angle. What we don't want is a page builder or a template engine that composes pages from angle data at runtime; that's scope we're deliberately cutting. Write the three pages directly, keep them simple and on-brand, and spend your time on requirement 5 instead.

Each page should message-match its angle: a visitor arriving on the fatigue page should immediately see fatigue, not a generic panel catalogue.

### **4\. Intent & Profile Capture**

Each page carries a **pop-up or short quiz** that captures who the visitor is and what they actually want. At minimum:

* **Demographics** \-- age and sex, which shape how we speak to someone and what registers as credible to them.  
* **Intent signals** \-- their sub-interest within the angle, what prompted them to look now, and any stated concern.  
* **Context** \-- which page and angle they came from, timestamp, and consent status.

One rule governs the form: **do not ask for information that will not change the experience.** Every field you collect should visibly influence what gets sent. If it doesn't, cut it.

Email capture requires **explicit consent**, recorded and auditable.

### **5\. Personalised Campaign Generation**

**This is the heart of the assignment and where most of your time should go.**

Once a visitor submits the form, the system generates and sends them a **welcome sequence of at least three emails**, written specifically for them.

Generation must draw on all of:

* **The angle** they arrived through \-- the campaign continues that conversation, it does not restart it.  
* **Their demographics** \-- age and sex shape **how the email is written and presented**, not what it claims clinically. A 28-year-old and a 55-year-old asking about the same thing should get noticeably different treatments: register and vocabulary, pacing, how much reassurance versus how much brevity, type size and visual density, imagery, and what reads as credible to that person.  
* **Their stated intent** \-- what they told the quiz, used directly and specifically.  
* **Their brand's voice and constraints** \-- tone, reading level, banned phrases, required compliance language.

The test we will apply: **two visitors who landed on the same page but differ in age, sex, or quiz answers must receive materially different emails.** Not the same template with a swapped first name \-- a different voice, a different emphasis, and a different look.

To be clear about the line: demographics drive **tone, styling, and emphasis**. They must not drive clinical recommendations \-- see requirement 6\.

**Sequence shape.** A welcome sequence should carry one job per email and build in this order:

1. **Deliver the promise and restate the angle** \-- confirm they're in the right place, in their own language.  
2. **Explain the mechanism** \-- how the thing actually works, in plain terms.  
3. **Handle the angle-specific objection** \-- the reason *this* person would hesitate, not a generic FAQ.

Beyond three emails, relevant proof and a truthful offer are natural next beats.

Practical notes:

* Sending can use any mail driver (Mailtrap, Mailhog) \-- just document your choice.  
* Handle rate limits, timeouts, provider errors, and malformed model output gracefully.

### **6\. Guardrails**

This is regulated territory, and these are release blockers rather than polish.

* **No unapproved health claims.** Generated copy must not promise diagnoses, cures, or outcomes we cannot support. What a brand may claim should be **defined in the system, not improvised by the model**.  
* **No clinical inference from demographics.** Knowing someone's age and sex tells us how to talk to them. It does not license the model to suggest what they might have, what they should test for, or what their results are likely to show. Personalisation stops at presentation.  
* **No false urgency**, fake scarcity, hidden conditions, or misleading comparisons. Deadlines and eligibility must be stated precisely and truthfully.  
* **Consent and suppression are enforced** \-- no email without recorded consent, and unsubscribed or converted visitors are promptly excluded from further sends.  
* **Subject lines must accurately represent the message.**

### **7\. Admin Dashboard**

An authenticated view that makes the loop **visible and auditable**. For any visitor, a reviewer should be able to trace: which page they landed on, which angle it came from, what they told the pop-up, what was generated for them, why it was generated that way, and what was actually sent.

Include an aggregate view showing performance by angle \-- captures, consent rate, and sends.

---

## **AI-Assisted Coding** 

You are **encouraged** to use AI-assisted coding tools. We'd love to evaluate *how* you use them. For example, include a CLAUDE.md (or equivalent) showing how you directed the tool, your prompting approach, and how you reviewed/validated the output.

---

## **Bonus Points**

These are optional but will strengthen your submission:

1. **SMS Branch** \-- Add a consented SMS layer alongside email. SMS is the concise, timely layer, not a compressed email: use it for confirmation, a single well-chosen objection, or a real deadline. Respect explicit consent, quiet hours, frequency caps, and simple opt-out.  
   * **No SMS provider integration is needed** \-- don't wire up Twilio or anything similar. Generate the messages, persist them, and surface them in the dashboard alongside the emails so we can see what would have been sent and why. The generation and the branching logic are what we're evaluating, not the delivery.  
2. **Follow-Up Branching** \-- Extend beyond the welcome sequence: branch the journey based on whether the visitor opened, clicked, or went quiet.  
3. **Angle Performance Comparison** \-- Compare angles side by side on capture and consent rates, so a reviewer can see which theory is working.

---

## **A Note on Tests**

Automated tests are **not required**. If you do write them, we're less interested in coverage numbers than in *what* you chose to test and why \-- a short note in your README explaining your testing decisions (including a decision not to write any) is welcome.

---

## **Submission Guidelines**

* Upload your project to a **private GitHub repository**. You'll be informed at a later stage on who to invite as collaborators.  
* Include a complete README.md covering setup, installation, environment configuration, how to run the project, and how intent flows from page to campaign.  
* **Walk us through one journey in the README.** Pick a visitor, show the page they landed on, what they answered, and the emails that resulted \-- with enough detail that we can follow the reasoning without running anything.  
* Provide seed data so reviewers can log in and see a populated application immediately (include admin credentials in the README). Seed **at least two brands with genuinely different identities and voices**, three angles, and a handful of visitors whose demographics and answers differ enough that the generated campaigns visibly diverge.

**Important:** As part of our review process we will run:

```
php artisan migrate:fresh --seed
```

on a fresh checkout and expect it to complete **without errors**. Please make sure all seeders are properly implemented, the database can be fully reset and reseeded without manual intervention, and any custom setup commands are documented in the README.

Good luck \-- we're looking forward to seeing what you build.

---

> 💡 **Pro Tips**

> * Treat this assignment as you would a real task in your day-to-day work. Make reasonable assumptions where needed.  
> * In real work, requirements aren't always perfect. Use your judgment \-- if something doesn't sit right with you, we'd love to hear how you'd handle it.  
> * We're not just reviewing *if* it works \-- we're also looking at code quality, security, attention to detail, and how you structure and approach the problem.

